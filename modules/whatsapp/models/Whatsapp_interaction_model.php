<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp_interaction_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all interaction messages from the database
     *
     * @return array Array of interaction messages
     */
public function get_interactions()
{
    // Fetch interactions ordered by time_sent in descending order
    $interactions = $this->db->order_by('time_sent', 'DESC')
                      ->get(db_prefix() . 'whatsapp_interactions')
                      ->result_array();

    // Fetch messages for each interaction
    foreach ($interactions as &$interaction) {
        $interaction_id = $interaction['id'];
        $messages = $this->get_interaction_messages($interaction_id);
        $this->map_interaction($interaction);
        $interaction['messages'] = $messages;

        // Fetch staff name for each message in the interaction
        foreach ($interaction['messages'] as &$message) {
            if (!empty($message['staff_id'])) {
                $message['staff_name'] = get_staff_full_name($message['staff_id']);
            } else {
                $message['staff_name'] = null;
            }

          // Check if URL is already a base name
            if ($message['url'] && strpos($message['url'], '/') === false) {
                // If URL doesn't contain "/", consider it as a file name
                // Assuming base URL is available
                $message['asset_url'] = WHATSAPP_MODULE_UPLOAD_URL . $message['url'];
            } else {
                // Otherwise, use the URL directly
                $message['asset_url'] = $message['url'] ?? null;
            }

        }
    }

    return $interactions;
}




    /**
     * Insert a new interaction message into the database
     *
     * @param array $data Data to be inserted
     * @return int Insert ID
     */
    public function insert_interaction($data)
    {
        $existing_interaction = $this->db->where('receiver_id', $data['receiver_id'])
            ->get(db_prefix() .'whatsapp_interactions')
            ->row();

        if ($existing_interaction) {
            $this->db->where('id', $existing_interaction->id)
                ->update(db_prefix() .'whatsapp_interactions', $data);
            return $existing_interaction->id;
        } else {
            $this->db->insert(db_prefix() .'whatsapp_interactions', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Get all interaction messages for a specific interaction ID
     *
     * @param int $interaction_id ID of the interaction
     * @return array Array of interaction messages
     */
    public function get_interaction_messages($interaction_id)
    {
        $this->db->where('interaction_id', $interaction_id)
            ->order_by('time_sent', 'asc');
        return $this->db->get(db_prefix() . 'whatsapp_interaction_messages')->result_array();
    }

    /**
     * Insert a new interaction message into the database
     *
     * @param array $data Data to be inserted
     * @return int Insert ID
     */
    public function insert_interaction_message($data)
    {
       // Assuming 'whatsapp_interaction_messages' is the table name
        $this->db->insert(db_prefix() .'whatsapp_interaction_messages', $data);
    
        // Check if the insert was successful
        if ($this->db->affected_rows() > 0) {
            // Return the ID of the inserted message
            return $this->db->insert_id();
        } else {
            // Return false if the insert failed
            return false;
        }
    }

    /**
     * Get the ID of the last message for a given interaction
     *
     * @param int $interaction_id ID of the interaction
     * @return int ID of the last message
     */
    public function get_last_message_id($interaction_id)
    {
        $this->db->select_max('id')
            ->where('interaction_id', $interaction_id);
        $query = $this->db->get(db_prefix() .'whatsapp_interaction_messages');
        $result = $query->row_array();
        return $result['id'];
    }

    /**
     * Update the status of a message in the database
     *
     * @param int $interaction_id ID of the interaction
     * @param string $status Status to be updated
     * @return void
     */
    public function update_message_status($interaction_id, $status)
    {
        $this->db->where('message_id', $interaction_id)
            ->update(db_prefix() . 'whatsapp_interaction_messages', ['status' => $status]);
    }

    /**
     * Map interaction data to entities based on receiver ID
     *
     * @param array $interaction interaction data
     * @return void
     */
    public function map_interaction($interaction)
    {

         if ($interaction['type'] !== null || $interaction['type_id'] !== null) {

            $interaction_id = $interaction['id'];
            $receiver_id = $interaction['receiver_id'];

            $customer = $this->db->where('phonenumber', $receiver_id)->get(db_prefix() .'clients')->row();
            $contact = $this->db->where('phonenumber', $receiver_id)->get(db_prefix() .'contacts')->row();
            $lead = $this->db->where('phonenumber', $receiver_id)->get(db_prefix() .'leads')->row();
            $staff = $this->db->where('phonenumber', $receiver_id)->get(db_prefix() .'staff')->row();

            $entity = null;
            $type = null;

            if ($customer) {
                $entity = $customer->userid;
                $type = 'customer';
            } elseif ($contact) {
                $entity = $contact->id;
                $type = 'contact';
            } elseif ($staff) {
                $entity = $staff->staffid;
                $type = 'staff';
            } else {
                if ($lead !== null) {
                    $entity = $lead->id;
                }

                $type = 'lead';

                $lead_data = array(
                    'phonenumber' => $receiver_id,
                    'name' => $interaction['name'],
                    'status' => get_option('whatsapp_lead_status'),
                    'assigned' => get_option('whatsapp_lead_assigned'),
                    'source' => get_option('whatsapp_lead_source')
                );

                $existing_lead = $this->db->where('phonenumber', $receiver_id)->get(db_prefix() .'leads')->row();
                if ($existing_lead) {
                    $this->db->where('phonenumber', $receiver_id)->update(db_prefix() .'leads', $lead_data);
                } else {
                    $this->db->insert(db_prefix() .'leads', $lead_data);
                }
            }

            $data = array(
                'type' => $type,
                'type_id' => $entity,
                'receiver_id' => $receiver_id
            );

            $existing_interaction = $this->db->where('id', $interaction_id)->get(db_prefix() .'whatsapp_interactions')->row();

            if ($existing_interaction) {
                $this->db->where('id', $interaction_id)->update(db_prefix() .'whatsapp_interactions', $data);
            } else {
                $data['id'] = $interaction_id;
                $this->db->insert(db_prefix() .'whatsapp_interactions', $data);
            }
        }
    }
}
