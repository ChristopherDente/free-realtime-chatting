<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $usersFile = __DIR__ . '/../data/users.csv';
    protected $chatsFile = __DIR__ . '/../data/chats.csv';
    protected $userMapping = []; // resourceId => username

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send chat history to the new user
        if (file_exists($this->chatsFile)) {
            $handle = fopen($this->chatsFile, "r");
            if ($handle) {
                fgetcsv($handle); // skip header
                $history = [];
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 3) {
                        $history[] = [
                            'type' => 'chat',
                            'timestamp' => $data[0],
                            'username' => $data[1],
                            'message' => $data[2]
                        ];
                    }
                }
                fclose($handle);
                // Send history only if there is any
                if (!empty($history)) {
                    $conn->send(json_encode(['type' => 'history', 'data' => $history]));
                }
            }
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data) return;

        if ($data['type'] === 'join') {
            $username = htmlspecialchars($data['username']);
            $this->userMapping[$from->resourceId] = $username;
            
            // Save user to CSV
            $fp = fopen($this->usersFile, 'a');
            fputcsv($fp, [$username, date('Y-m-d H:i:s')]);
            fclose($fp);
            
            // Broadcast join message
            $broadcastData = json_encode([
                'type' => 'system',
                'message' => "{$username} has joined the chat."
            ]);
            
            foreach ($this->clients as $client) {
                $client->send($broadcastData);
            }
        } elseif ($data['type'] === 'chat') {
            $username = $this->userMapping[$from->resourceId] ?? 'Unknown';
            $message = htmlspecialchars($data['message']);
            $timestamp = date('Y-m-d H:i:s');
            
            // Save to CSV
            $fp = fopen($this->chatsFile, 'a');
            fputcsv($fp, [$timestamp, $username, $message]);
            fclose($fp);
            
            $broadcastData = json_encode([
                'type' => 'chat',
                'timestamp' => $timestamp,
                'username' => $username,
                'message' => $message
            ]);
            
            foreach ($this->clients as $client) {
                $client->send($broadcastData);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $username = $this->userMapping[$conn->resourceId] ?? 'Unknown';
        unset($this->userMapping[$conn->resourceId]);
        
        echo "Connection {$conn->resourceId} has disconnected\n";
        
        if ($username !== 'Unknown') {
            $broadcastData = json_encode([
                'type' => 'system',
                'message' => "{$username} has left the chat."
            ]);
            
            foreach ($this->clients as $client) {
                $client->send($broadcastData);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
