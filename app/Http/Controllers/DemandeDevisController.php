<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DemandeDevisController extends Controller
{
    public function show(Request $request)
    {
        $sujet = $request->query('sujet');
        $sujetLabel = match ($sujet) {
            'devis' => 'Demande de devis',
            'urgent' => 'Demande de delais courts',
            'technique' => 'Question technique',
            'sav' => 'SAV',
            default => null,
        };

        return view('contact.devis', compact('sujet', 'sujetLabel'));
    }

    public function send(Request $request)
    {
        // Anti-bot : honeypot rempli = bot
        if ($request->filled('website_url')) {
            return redirect()->route('devis')->with('devis-sent', true);
        }

        // Anti-bot : formulaire soumis trop vite (< 3 secondes) = bot
        $timestamp = (int) $request->input('_timestamp', 0);
        if ($timestamp > 0 && (time() - $timestamp) < 3) {
            return redirect()->route('devis')->with('devis-sent', true);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'societe' => 'nullable|string|max:255',
            'quantite' => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
            'fichiers' => 'nullable|array|max:5',
            'fichiers.*' => 'file|max:10240|mimes:png,jpg,jpeg,pdf,ai,eps,psd,svg',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email n\'est pas valide.',
            'message.required' => 'Veuillez decrire votre projet.',
            'fichiers.max' => '5 fichiers maximum.',
            'fichiers.*.max' => 'Chaque fichier ne doit pas depasser 10 Mo.',
        ]);

        // Stocker les fichiers
        $attachments = [];
        if ($request->hasFile('fichiers')) {
            foreach ($request->file('fichiers') as $file) {
                $path = $file->store('demandes-devis/' . date('Y-m'), 'public');
                $attachments[] = [
                    'path' => Storage::disk('public')->path($path),
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        // Adresse de destination (configurable dans .env)
        $to = config('mail.devis_to', 'contact@marquage-textile.fr');

        Mail::raw($this->buildMailBody($validated), function ($mail) use ($to, $validated, $attachments) {
            $mail->to($to)
                ->replyTo($validated['email'], $validated['nom'])
                ->subject('Demande de devis — ' . $validated['nom']);

            foreach ($attachments as $attachment) {
                $mail->attach($attachment['path'], [
                    'as' => $attachment['name'],
                    'mime' => $attachment['mime'],
                ]);
            }
        });

        return back()->with('devis-sent', true);
    }

    private function buildMailBody(array $data): string
    {
        $body = "DEMANDE DE DEVIS\n";
        $body .= "================\n\n";
        $body .= "Nom : {$data['nom']}\n";
        $body .= "Email : {$data['email']}\n";

        if (! empty($data['telephone'])) {
            $body .= "Telephone : {$data['telephone']}\n";
        }
        if (! empty($data['societe'])) {
            $body .= "Societe : {$data['societe']}\n";
        }
        if (! empty($data['quantite'])) {
            $body .= "Quantite : {$data['quantite']}\n";
        }

        $body .= "\nMessage :\n{$data['message']}\n";
        $body .= "\n---\nEnvoye depuis marquage-textile.fr le " . now()->format('d/m/Y a H:i');

        return $body;
    }
}
