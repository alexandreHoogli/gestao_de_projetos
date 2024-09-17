<?php
defined('BASEPATH') or exit('No direct script access allowed');
use GuzzleHttp\Client;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class Webhook extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        // Load necessary models or libraries here
        $this->load->model('whatsapp_interaction_model');
        $this->load->library('WhatsappLibrary'); // Load and instantiate WhatsappLibrary
    }

    public function getdata()
    {
        // Verify token
        $verify_token = get_option('whatsapp_webhook_token') ?? '123456';
        // Check if the request method is GET (for initial verification)
        if ($this->input->get('hub_verify_token') && $this->input->get('hub_challenge')) {
            $hub_verify_token = $this->input->get('hub_verify_token');
            $challenge = $this->input->get('hub_challenge');

            // Verify the hub_verify_token
            if ($hub_verify_token === $verify_token) {
                // Respond with the challenge to complete the verification
                echo $challenge;
                log_message('error', 'Verification successful. Challenge: ' . $challenge);
                return; // End processing for verification request
            } else {
                log_message('error', 'Verification failed. Invalid verify token: ' . $hub_verify_token);
                $this->output
                    ->set_status_header(403)
                    ->set_output('Invalid verify token');
                return; // End processing for invalid token
            }
        }

        // Handle messages received from WhatsApp
        $payload = json_decode(file_get_contents('php://input'), true);
        log_message('error', 'Webhook payload: ' . print_r($payload, true));
// Check if payload contains messages
if (isset($payload['entry'][0]['changes'][0]['value']['messages'])) {
    $messageEntry = $payload['entry'][0]['changes'][0]['value']['messages'][0];
    $name = $payload['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'];
    $from = $messageEntry['from'];
    $messageType = $messageEntry['type'];
    $message_id = $payload['entry'][0]['changes'][0]['value']['messages'][0]['id'];

    // Extract message content based on type
   switch ($messageType) {
    case 'text':
        $message = $messageEntry['text']['body'];
        break;
    case 'interactive':
        $message = $messageEntry['interactive']['list_reply']['title'];
        break;
    case 'button':
        $message = $messageEntry['button']['text'];
        break;
    case 'reaction':
    $emoji = $messageEntry['reaction']['emoji'];
$decodedEmoji = json_decode('"' . $emoji . '"', false, 512, JSON_UNESCAPED_UNICODE);

    $message = $decodedEmoji;
    log_message('error', 'Verification successful. Challenge: ' . $emojiUnicodeEscapeSequence);

        break;
    case 'image':
    case 'audio':
    case 'document':
    case 'video':
        $media_id = $messageEntry[$messageType]['id'];
        $caption = $messageEntry[$messageType]['caption'] ?? null;
        $access_token = get_option('whatsapp_access_token'); // Assuming $cloudapis is defined somewhere
        $whatsappLibrary = new WhatsappLibrary();
        $attachment = $whatsappLibrary->retrieveUrl($media_id, $access_token);
        log_message('error', 'Verification successful. Challenge: ' . $attachment);
        break;
    default:
        $message = ''; // Default to empty string
        break;
}

// Save message to database
$interaction_id = $this->whatsapp_interaction_model->insert_interaction([
    'receiver_id' => $from,
    'name' => $name, // Assuming $name is defined somewhere
    'last_message' => $message ?? $messageType,
    'time_sent' => date("Y-m-d H:i:s")
]);

// Insert interaction message data into the 'whatsapp_official_interaction_messages' table
$this->whatsapp_interaction_model->insert_interaction_message([
    'interaction_id' => $interaction_id,
    'sender_id' => $from,
    'message_id' => $message_id,
    'message' => $message ?? $caption ?? "-",
    'type' => $messageType,
    'staff_id' => get_staff_user_id() ?? null,
    'url' => $attachment ?? null,
    'status' => 'sent',
    'time_sent' => date("Y-m-d H:i:s")
]);

// Create an array containing relevant message data for the Pusher event payload
$messageData = [
    'sender_id' => $from,
    'message_id' => $message_id,
    'message' => $message ?? $caption ?? "-",
    'type' => $messageType,
    'staff_id' => get_staff_user_id() ?? null,
    'url' => $attachment ?? null,
    'status' => 'sent',
    'time_sent' => date("Y-m-d H:i:s")
];


    http_response_code(200);
}elseif (isset($payload['entry'][0]['changes'][0]['value']['statuses'])){
$id = $payload['entry'][0]['changes'][0]['value']['statuses'][0]['id'];
$status = $payload['entry'][0]['changes'][0]['value']['statuses'][0]['status'];
$this->whatsapp_interaction_model->update_message_status($id,$status);
http_response_code(200);
} else {
    // Invalid payload structure
    log_message('error', 'Payload does not contain expected data structure.');
    $this->output
        ->set_status_header(400)
        ->set_output('Invalid payload structure');
}

    }
public function send_message() {
    // Retrieve POST data
    $to = $_POST['to'] ?? '';
    $message = $_POST['message'] ?? '';
    $imageAttachment = $_FILES['image'] ?? null;
    $videoAttachment = $_FILES['video'] ?? null;
    $documentAttachment = $_FILES['document'] ?? null;
    $audioAttachment = $_FILES['audio'] ?? null;

    // Initialize message data
    $message_data = [];

    // Check if there is only text message or only attachment
    if (!empty($message)) {
        // Send only text message
        $message_data[] = [
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message
            ]
        ];
    } 

    // Handle audio attachment
    if (!empty($audioAttachment)) {
                $whatsappLibrary = new WhatsappLibrary();
$audio_url = $whatsappLibrary->handle_audio_upload($audioAttachment);
        $message_data[] = [
            'type' => 'audio',
            'audio' => [
                'url' => WHATSAPP_MODULE_UPLOAD_URL . $audio_url  // Prepend base URL to audio file name
            ]
        ];
    }

    // Handle image attachment
    if (!empty($imageAttachment)) {
                     $whatsappLibrary = new WhatsappLibrary();
   $image_url = $whatsappLibrary->handle_attachment_upload($imageAttachment);
        $message_data[] = [
            'type' => 'image',
            'image' => [
                'url' => WHATSAPP_MODULE_UPLOAD_URL . $image_url  // Prepend base URL to image file name
            ]
        ];
    }

    // Handle video attachment
    if (!empty($videoAttachment)) {
                         $whatsappLibrary = new WhatsappLibrary();
    $video_url = $whatsappLibrary->handle_attachment_upload($videoAttachment);
        $message_data[] = [
            'type' => 'video',
            'video' => [
                'url' => WHATSAPP_MODULE_UPLOAD_URL . $video_url  // Prepend base URL to video file name
            ]
        ];
    }

    // Handle document attachment
    if (!empty($documentAttachment)) {
    $whatsappLibrary = new WhatsappLibrary();
   $document_url = $whatsappLibrary->handle_attachment_upload($documentAttachment);
   
        $message_data[] = [
            'type' => 'document',
            'document' => [
                'url' => WHATSAPP_MODULE_UPLOAD_URL . $document_url  // Prepend base URL to document file name
            ]
        ];
    }

    // Send message using WhatsApp Cloud API
    // Instantiate the WhatsAppCloudApi super class.
    $whatsapp_cloud_api = new WhatsAppCloudApi([
        'from_phone_number_id' => get_option('phone_number_id'),
        'access_token' => get_option('whatsapp_access_token'),
    ]);

$messageId = null;

foreach ($message_data as $data) {
    switch ($data['type']) {
        case 'text':
            $response = $whatsapp_cloud_api->sendTextMessage($to, $data['text']['body']);
            break;
        case 'audio':
            $response = $whatsapp_cloud_api->sendAudio($to, new LinkID($data['audio']['url']));
            break;
        case 'image':
            $response = $whatsapp_cloud_api->sendImage($to, new LinkID($data['image']['url']));
            break;
        case 'video':
            $response = $whatsapp_cloud_api->sendVideo($to, new LinkID($data['video']['url']));
            break;
        case 'document':
            $fileName = basename($data['document']['url']);
            $response = $whatsapp_cloud_api->sendDocument($to, new LinkID($data['document']['url']), $fileName, 'Document');
            break;
    }
    
    // Decode the response JSON
    $response_data = $response->decodedBody();

    // Check if the response data contains the message ID
    if (isset($response_data['messages'][0]['id'])) {
        // Message sent successfully, store the message ID
        $messageId = $response_data['messages'][0]['id'];
    }
}
    
    
    // Insert message into the database
    $interaction_id = $this->whatsapp_interaction_model->insert_interaction([
        'receiver_id' => $to,
        'last_message' => $message ?? ($message_data[0]['type'] ?? ''), // Ensure fallback in case message_data is not set
        'time_sent' => date("Y-m-d H:i:s")
    ]);

    foreach ($message_data as $data) {
        $this->whatsapp_interaction_model->insert_interaction_message([
            'interaction_id' => $interaction_id,
            'sender_id' => get_option('phone_number'),
            'message' => $message,'message_id' => $messageId,
            'type' => $data['type'] ?? '', // Ensure fallback in case message_data['type'] is not set
            'staff_id' => get_staff_user_id() ?? null,
            'url' => basename($data[$data['type']]['url'] ?? null),
            'status' => 'sent',
            'time_sent' => date("Y-m-d H:i:s")
        ]);
    }

    // Return success response
    echo json_encode(['success' => true]);
}



public function mark_interaction_as_read() {
    // Retrieve POST data
    $interaction_id = $_POST['interaction_id'] ?? '';

    // Validate input
    if (empty($interaction_id)) {
        echo json_encode(['error' => 'Invalid interaction ID']);
        return;
    }

    // Call the model function to mark the interaction as read
    $success = $this->whatsapp_interaction_model->mark_interaction_as_read($interaction_id);

    // Check if the interaction was successfully marked as read
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to mark interaction as read']);
    }
}


}
