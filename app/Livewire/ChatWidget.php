<?php

namespace App\Livewire;

use App\Models\ChatConversation;
use App\Models\Order;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ChatWidget extends Component
{
    public bool $open = false;

    public string $screen = 'menu'; // menu, faq, order_lookup, file_formats, delais, live_intro, live

    public string $message = '';

    public string $orderRef = '';

    public ?string $orderStatus = null;

    public ?int $conversationId = null;

    public array $chatMessages = [];

    // Rate limiting
    private const MAX_MESSAGES_PER_MINUTE = 10;

    public function mount(): void
    {
        // Reprendre une conversation existante si cookie present
        $token = request()->cookie('chat_token');
        if ($token) {
            $conv = ChatConversation::where('session_token', $token)
                ->whereIn('status', ['bot', 'waiting', 'active'])
                ->first();
            if ($conv) {
                $this->conversationId = $conv->id;
                $this->loadMessages();
                if (in_array($conv->status, ['waiting', 'active'])) {
                    $this->screen = 'live';
                }
            }
        }
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function goTo(string $screen): void
    {
        $this->screen = $screen;
        $this->orderStatus = null;
        $this->orderRef = '';
    }

    // ─── FAQ Scenarios ───────────────────────────────────

    public function getFaqItems(): array
    {
        return [
            ['q' => 'Comment commander du marquage textile ?', 'a' => 'Choisissez vos textiles dans notre catalogue, ajoutez-les au devis, selectionnez votre technique de marquage et la taille du logo. Validez votre devis, passez commande et envoyez-nous votre fichier. Notre equipe s\'occupe du reste !'],
            ['q' => 'Quels sont les delais ?', 'a' => '__delais__'],
            ['q' => 'Quel type de fichier utiliser ?', 'a' => '__file_formats__'],
            ['q' => 'A-t-on une maquette avant de commander ?', 'a' => 'Oui ! Apres commande, notre equipe PAO vous envoie un BAT (Bon A Tirer) par email avec la maquette de votre marquage. Vous pouvez valider ou demander des modifications autant de fois que necessaire. La production ne demarre qu\'apres votre validation.'],
            ['q' => 'Quel est le minimum de commande ?', 'a' => 'Le minimum varie selon la technique : 10 pieces pour le DTF et DTG, 20 pieces pour la broderie et la serigraphie, 50 pieces pour le transfert numerique et serigraphique.'],
            ['q' => 'Quels sont les modes de paiement ?', 'a' => 'Nous acceptons le paiement par carte bancaire (Visa, Mastercard) et par virement bancaire. Un acompte de 50% est possible pour les grosses commandes.'],
            ['q' => 'Livrez-vous en dehors de la France ?', 'a' => 'Oui, nous livrons dans toute l\'Union Europeenne, en Suisse et dans les DOM-TOM. Les frais de port sont calcules automatiquement selon la destination.'],
            ['q' => 'Peut-on mettre des prenoms sur les textiles ?', 'a' => 'Oui ! Vous pouvez personnaliser chaque textile avec un prenom. Il suffit de le preciser lors de votre commande. La broderie et le flex sont les techniques les plus adaptees pour la personnalisation individuelle par prenom.'],
            ['q' => 'Quelle technique pour mon projet ?', 'a' => 'Le bloc devis vous propose la solution la moins chere et la solution premium recommandee en fonction des informations textiles et du visuel a marquer. Il vous suffit d\'ajouter vos textiles au devis, de configurer votre logo (taille, nombre de couleurs) et les recommandations s\'affichent automatiquement. La broderie est ideale pour un rendu premium, la serigraphie pour les grandes quantites, le DTF pour les petites series avec beaucoup de couleurs.'],
        ];
    }

    public function getFileFormats(): array
    {
        // TODO 2b: MarkingTechnique dégagé. Méthode neutralisée.
        return [];
    }

    // ─── Order lookup (lecture seule) ────────────────────

    public function lookupOrder(): void
    {
        $ref = strip_tags(trim($this->orderRef));
        if (! $ref || strlen($ref) < 5) {
            $this->orderStatus = 'Veuillez entrer une reference valide (ex: CMD202604001).';
            return;
        }

        // Lecture seule : on ne retourne que le statut texte
        $order = Order::where('reference', $ref)->first(['status', 'payment_status', 'reference', 'status_prep', 'status_production', 'status_shipping', 'shipped_at', 'tracking_number']);

        if (! $order) {
            $this->orderStatus = 'Aucune commande trouvee avec la reference "' . e($ref) . '".';
            return;
        }

        // Déterminer l'étape réelle
        $steps = [];

        // Paiement
        $steps[] = match ($order->payment_status) {
            'paid' => 'Paiement recu',
            'partial' => 'Acompte recu — solde en attente',
            'pending' => 'En attente de paiement',
            default => '',
        };

        // TODO 2b: bloc BAT supprimé (workflow BAT dégagé)

        // Préparation & Production
        if ($order->status_prep === 'done' && $order->status_production !== 'done') {
            $steps[] = 'En preparation';
        }
        if ($order->status_production === 'done' && $order->status_shipping !== 'done' && !in_array($order->status, ['shipped', 'delivered'])) {
            $steps[] = 'En production';
        }

        // Prêt à expédier / Expédié / Livré
        if ($order->status === 'shipped') {
            $steps[] = 'Production terminee';
            $steps[] = 'Expediee' . ($order->tracking_number ? ' — Suivi : ' . $order->tracking_number : '');
        } elseif ($order->status === 'delivered') {
            $steps[] = 'Production terminee';
            $steps[] = 'Livree';
        } elseif ($order->status_shipping === 'done') {
            $steps[] = 'Production terminee — prete a etre expediee';
        }

        if ($order->status === 'cancelled') {
            $steps = ['Commande annulee'];
        }

        $stepsText = implode("\n", array_filter($steps));
        $this->orderStatus = "Commande {$order->reference} :\n{$stepsText}";
    }

    // ─── Live chat ───────────────────────────────────────

    public function startLiveChat(): void
    {
        $conv = $this->getOrCreateConversation();
        $conv->update(['status' => 'waiting']);
        $conv->addMessage('Le client demande a parler a un conseiller.', 'bot');
        $this->screen = 'live';
        $this->loadMessages();

        // Notifier par mail
        try {
            $adminEmails = [
                'contact@marquage-textile.fr',
            ];
            foreach (array_filter($adminEmails) as $email) {
                \Illuminate\Support\Facades\Mail::raw(
                    "Un client demande a parler a un conseiller sur le chat en direct.\n\n"
                    . "Repondez depuis : " . url('/admin/live-chat') . "\n\n"
                    . "Conversation #" . $conv->id,
                    fn ($m) => $m->to($email)->subject('[CHAT] Client en attente — Marquage Textile')
                );
            }
        } catch (\Throwable $e) {
            // Ne pas bloquer le chat si le mail echoue
        }
    }

    public function sendMessage(): void
    {
        $msg = strip_tags(trim($this->message));
        if ($msg === '' || strlen($msg) > 2000) {
            return;
        }

        // Rate limiting simple
        $conv = $this->getOrCreateConversation();
        $recentCount = $conv->messages()
            ->where('sender', 'visitor')
            ->where('created_at', '>=', now()->subMinute())
            ->count();

        if ($recentCount >= self::MAX_MESSAGES_PER_MINUTE) {
            return;
        }

        $conv->addMessage($msg, 'visitor');

        if ($conv->status === 'bot') {
            $conv->update(['status' => 'waiting']);
        }

        $this->message = '';
        $this->loadMessages();
    }

    public function pollMessages(): void
    {
        if ($this->conversationId && $this->screen === 'live') {
            $conv = ChatConversation::find($this->conversationId);

            // Conversation fermée par l'admin ou le timeout
            if ($conv && $conv->status === 'closed') {
                $this->loadMessages();
                // Laisser voir le dernier message 3 secondes puis retour menu
                $lastMsg = $conv->messages()->latest()->first();
                if ($lastMsg && $lastMsg->created_at->diffInSeconds(now()) > 3) {
                    $this->conversationId = null;
                    $this->chatMessages = [];
                    $this->screen = 'menu';
                }
                return;
            }

            $this->loadMessages();
            if ($conv && $conv->status === 'waiting') {
                $waitingSince = $conv->messages()->where('sender', 'bot')->latest()->value('created_at');
                if ($waitingSince && $waitingSince->diffInMinutes(now()) >= 5) {
                    $hasAdminReply = $conv->messages()->where('sender', 'admin')->exists();
                    if (! $hasAdminReply) {
                        $conv->addMessage('Aucun conseiller n\'est disponible pour le moment. Laissez-nous un message avec votre email et nous vous recontacterons dans les meilleurs delais.', 'bot');
                        $conv->update(['status' => 'closed']);
                        $this->conversationId = null;
                        $this->chatMessages = [];
                        $this->screen = 'menu';
                    }
                }
            }
        }
    }

    private function loadMessages(): void
    {
        if (! $this->conversationId) {
            $this->chatMessages = [];
            return;
        }

        $this->chatMessages = ChatConversation::find($this->conversationId)
            ?->messages()
            ->orderBy('created_at')
            ->get(['sender', 'body', 'created_at'])
            ->map(fn ($m) => [
                'sender' => $m->sender,
                'body' => e($m->body),
                'time' => $m->created_at->format('H:i'),
            ])
            ->toArray() ?? [];
    }

    private function getOrCreateConversation(): ChatConversation
    {
        if ($this->conversationId) {
            $conv = ChatConversation::find($this->conversationId);
            if ($conv) {
                return $conv;
            }
        }

        $token = Str::random(48);
        $conv = ChatConversation::create([
            'session_token' => $token,
            'status' => 'bot',
        ]);

        $this->conversationId = $conv->id;

        // Set cookie for 24h
        cookie()->queue('chat_token', $token, 1440);

        return $conv;
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
