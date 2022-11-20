<?php


namespace Plugin\AI;

use Bot\Event\ChatEvent;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Console\ErrorFormat;
use GuzzleHttp\Client;

class AI extends PhpPlugin
{
    public function onChat(ChatEvent $event)
    {
        if (substr_count($event->getMessage(), $this->bot->username)) {
            try {
                $message = $event->message;
                if(strpos($message, '早安') !== false || strpos($message, '午安') !== false || strpos($message, '晚安') !== false ){
                    return;
                }
                $message = str_replace(' [*'.$this->bot->username.'*] ', '菲菲', $message);
                $message = str_replace(' ', '', $message);
                $client = new Client();
                $response = $client->get('http://api.qingyunke.com/api.php', [
                        'query' => [
                            'key' => 'free',
                            'appid' => '0',
                            'msg' => $message
                        ]
                    ]);
                $result = json_decode($response->getBody(), true);
                $return = $result['content'];
                $return = str_replace('{br}', "\n", $return);
                $return = str_replace('菲菲', $this->bot->username, $return);
                $event->getSender()->sendMessage($return);
            } catch (\Exception $e) {
                $event->sender->sendRawMessage('\\\\\( **警告：** 出现了一些未知错误，但请不要修复我！','B54434');
                ErrorFormat::dump($e);
            }
        }
    }
}
