<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// use Twilio\Security\RequestValidator;


class WhatsAppController extends Controller
{
    public function getCredentials(){
        $cred = ["verif"=>env('TWILIO_VERIFY_TOKEN'),"sid"=>env('TWILIO_ACCOUNT_SID'),"token"=>env('TWILIO_AUTH_TOKEN'),
                "number"=>env('TWILIO_WHATSAPP_NUMBER')
    ];
    dd($cred);
    }



    // Remove debug method if not needed
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
            'menu' => "1. Support\n2. Prices\n3. Contact", // Fixed newlines
            'prices' => "Our pricing plans:\n- Basic: Free\n- Premium: $9.99/month\n- Pro: $19.99/month",
    'contact' => "You can reach us at:\n📞 +263782678233\n📧 info@varsityconnect.com",
    'services' => "We offer:\n- University Updates\n- Application Assistance\n- AI-Powered Guidance\n- More...",
    'apply' => "To apply for a university, visit our portal at https://varsityconnect.com/apply",
    'deadline' => "You can check university application deadlines at https://varsityconnect.com/deadlines",
    'gre' => "Find universities requiring or waiving GRE at https://varsityconnect.com/gre-requirements",
    'ielts' => "Check IELTS/English proficiency requirements at https://varsityconnect.com/english-requirements",
    'thank you' => "You're welcome! 😊 Let me know if you need anything else.",
    'bye' => "Goodbye! Have a great day. 👋",
    'default' => "Sorry, I didn't understand that message. Reply with 'menu' to see options.",
            default => 'Sorry, I didn\'t understand that message.',
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
                'To' => $to, // Already includes 'whatsapp:' prefix
                'Body' => $message,
            ]);

        // Optional error handling
        if ($response->failed()) {
            \Log::error('Twilio API Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        }
    }

}
