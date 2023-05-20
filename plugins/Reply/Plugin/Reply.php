<?php

namespace Plugin\Reply;

use Bot\Event\ChatEvent;
use Bot\Event\CommandEvent;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Models\Sender;
use Bot\Provider\IIROSE\Packets\SourcePacket;
use Plugin\Master\Master;
use Models\Bot;
use Models\BotPlugin;

class Reply extends PhpPlugin
{
    public function onCommand(CommandEvent $event)
    {
        if (substr($event->sign, 0, 6) == 'Reply:') {
            if (!Master::isMaster($event->sender->getUserId())) {
                return $event->sender->sendMessage('you are not my master');
            }
            if ($event->sign == 'Reply:Toogle') {
                if ($this->config['toogle'] == 0) {
                    $this->config['toogle'] = 1;
                } else {
                    $this->config['toogle'] = 0;
                }
                $event->sender->sendMessage('Reply toogle ' . ($this->config['toogle'] == 0 ? 'off' : 'on'));
            } elseif ($event->sign == 'Reply:Status') {
                IIROSEProvider::$instance->packet(new SourcePacket($event->sender->getUserId(), 'toogle: ' . $this->config['toogle'], ''));
                $event->sender->sendMessage('send status to you');
            } elseif ($event->sign == 'Reply:AddWord') {
                $word = $event->input->getArgument('word');
                $reply = $event->input->getArgument('reply');
                if(!isset($this->config['words'])) {
                    $this->config['words'] = [];
                }
                if($word === null) {
                    return $event->sender->sendMessage('word list: ' . implode(', ', array_keys($this->config['words'])));
                }
                if($reply === null) {
                    unset($this->config['words'][$event->input->getArgument('word')]);
                    $event->sender->sendMessage('remove word successful');
                }elseif (isset($this->config['words'][$word]) && $this->config['words'][$word] === $reply) {
                    $event->sender->sendMessage('word already exist');
                } else {
                    $event->sender->sendMessage(isset($this->config['words'][$word]) ? 'update word successful' : 'add word successful');
                    $this->config['words'][$word] = $reply;
                }
            }
            $bot = Bot::where('uid', '=', $this->bot->uid)->first();
            $botPlugin = BotPlugin::where('bot_id', $bot->id)->where('slug', 'Reply')->first();
            $botPlugin->bot_id = $bot->id;
            $botPlugin->slug = 'Reply';
            $botPlugin->configure = json_encode($this->config);
            $botPlugin->save();
        }
    }
    
    public function ChatEvent(ChatEvent $event)
    {
        if(substr(strtolower($event->message), 0, 5) == 'exec ') {
            return;
        }
        if ($this->config['toogle'] == 0) {
            return;
        }
        if (!isset($this->config['words'])) {
            return;
        }
        if (strpos($event->message, '(hr_)') !== false) {
            return;
        }
        if (rand(1, 3) == 1) {
            foreach ($this->config['words'] as $word => $reply) {
                if (strpos($event->message, $word) !== false) {
                    return $event->getSender()->sendRawMessage($event->message . ' (_hr) ' . $event->user_name . '_' . $event->id . ' (hr_) ' .$reply);
                }
            }
        }
    }  
}
