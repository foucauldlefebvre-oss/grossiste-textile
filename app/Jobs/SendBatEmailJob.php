<?php

namespace App\Jobs;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBatEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
    ) {}

    public function handle(): void
    {
        $user = $this->quote->user;

        if (! $user || ! $user->email) {
            Log::warning("SendBatEmailJob: pas d'email pour le devis {$this->quote->reference}");

            return;
        }

        $url = $this->quote->bat_client_url;

        Mail::raw(
            "Bonjour,\n\nVotre Bon À Tirer est disponible.\nCliquez ici pour le consulter et valider : {$url}\n\nCordialement,\nMarquage Textile",
            function ($message) use ($user) {
                $message->from(config('mail.pao_from', 'pao@marquage-textile.fr'), 'Marquage Textile - PAO')
                    ->to($user->email)
                    ->subject('Votre BAT est prêt — Devis '.$this->quote->reference);
            }
        );
    }
}
