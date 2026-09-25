<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Models\MailSetting;
use App\Support\MailConfigurator;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function sendMail(ContactRequest $request, MailConfigurator $mail)
    {
        $message = $this->storeInDatabase($request);
        $notified = $this->notifyByEmail($mail, $request, $message?->id);

        if ($message === null && ! $notified) {
            return redirect()->back()->withInput()->with('error', __('contact.error'));
        }

        return redirect()->back()->with('success', __('contact.success'));
    }

    /**
     * L'échec d'un seul des deux canaux (base ou email) ne doit jamais être
     * remonté au visiteur : l'autre canal suffit à remettre le message.
     */
    protected function storeInDatabase(ContactRequest $request): ?ContactMessage
    {
        try {
            return ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message,
                'locale' => app()->getLocale(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Contact message could not be stored.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function notifyByEmail(MailConfigurator $mail, ContactRequest $request, ?int $messageId): bool
    {
        $setting = MailSetting::query()->first();

        if (! $setting) {
            return false;
        }

        try {
            $mail->sendContact($setting, [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Contact email notification failed.', [
                'contact_message_id' => $messageId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
