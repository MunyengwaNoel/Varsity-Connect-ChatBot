<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// use Twilio\Security\RequestValidator;


class WhatsAppController extends Controller
{
    public function verify(Request $request)
    {
        $verifyToken = env('TWILIO_VERIFY_TOKEN');
        $challenge = $request->query('hub_challenge');

        if ($request->query('hub.verify_token') === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Invalid verification token', 403);
    }

    public function handle(Request $request)
    {
        // Add Twilio signature validation
        // $validator = new RequestValidator(env('TWILIO_AUTH_TOKEN'));
        // if (!$validator->validate(
        //     $request->header('X-Twilio-Signature'),
        //     $request->fullUrl(),
        //     $request->toArray()
        // )) {
        //     abort(403, 'Invalid request signature');
        // }

        $input = $request->all();
        $message = $input['Body'] ?? '';
        $from = $input['From'] ?? '';

        $response = $this->processMessage($message);
        $this->sendReply($from, $response);

        return response('', 200);
    }

    private function processMessage(string $message): string
    {
        $message = strtolower(trim($message));
        
        return match($message) {
            'hello' => 'Hi! How can I help you?',
            'hi'=>'Hi! How can I help you?',
            'menu' => "1. Support\n2. Prices\n3. Contact",
            default => UserController::welcome(),
        };
    }

    private function sendReply(string $to, string $message)
    {
        $twilioSid = env('TWILIO_ACCOUNT_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');

        // Validate credentials
        if (empty($twilioSid) || empty($twilioToken) || empty($twilioWhatsAppNumber)) {
            throw new \Exception('Twilio credentials not configured');
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/$twilioSid/Messages.json";

        $response = Http::asForm()
            ->withBasicAuth($twilioSid, $twilioToken)
            ->post($url, [
                'From' => "whatsapp:$twilioWhatsAppNumber",
                'To' => $to, 
                'Body' => $message,
            ]);

        if ($response->failed()) {
            \Log::error('Twilio API Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        }
    }

}
