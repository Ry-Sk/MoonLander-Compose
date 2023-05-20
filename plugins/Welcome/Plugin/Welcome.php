<?php

namespace Plugin\Welcome;

use Bot\Event\CommandEvent;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Models\Sender;
use Bot\Provider\IIROSE\Event\JoinEvent;
use Bot\Provider\IIROSE\Packets\ChatPacket;
use Models\Bot;
use Models\BotPlugin;
use Plugin\Master\Master;

class Welcome extends PhpPlugin
{
    public function onCommand(CommandEvent $event)
    {
        if (substr($event->sign, 0, 8) == 'Welcome:') {
            if (!Master::isMaster($event->sender->getUserId())) {
                $event->sender->sendMessage('you are not my master');
                return;
            }
        }
        if ($event->sign == 'Welcome:Toogle') {
            if ($this->config['toogle'] == 0) {
                $this->config['toogle'] = 1;
            } else {
                $this->config['toogle'] = 0;
            }
            $event->sender->sendMessage('Welcome toogle ' . ($this->config['toogle'] == 0 ? 'off' : 'on'));
        } elseif ($event->sign == 'Welcome:Add') {
            $userid = explode('@]', explode('[@', strtolower($event->input->getArgument('userid')))[1])[0];
            $msg = $event->input->getArgument('msg');
            if(!isset($this->config['welcome'])) {
                $this->config['welcome'] = [];
            }
            if($userid === null) {
                return $event->sender->sendMessage('users list: ' . implode(', ', array_keys($this->config['welcome'])));
            }
            if($msg === null) {
                unset($this->config['welcome'][$userid]);
                $event->sender->sendMessage('remove user successful');
            }elseif (isset($this->config['welcome'][$userid]) && $this->config['welcome'][$userid] === $msg) {
                $event->sender->sendMessage('user already exist');
            } else {
                $event->sender->sendMessage(isset($this->config['welcome'][$userid]) ? 'update user successful' : 'add user successful');
                $this->config['welcome'][$userid] = $msg;
            }
        }
        $bot= Bot::where('uid', '=', $this->bot->uid)->first();
        $botPlugin=BotPlugin::where('bot_id',$bot->id)->where('slug','Welcome')->first();
        $botPlugin->bot_id=$bot->id;
        $botPlugin->slug='Welcome';
        $botPlugin->configure=json_encode($this->config);
        $botPlugin->save();
    }
    public function onJoin(JoinEvent $event)
    {
        if ($this->config['toogle'] == 0) {
            return;
        }
        if(rand(1, 6) == 1) {
            if (isset($this->config['welcome'][$event->user_id])) {
                IIROSEProvider::$instance->packet(new ChatPacket($this->config['welcome'][$event->user_id],$event->color));
                return;
            }
            if (Master::isMember($event->user_id)){
                IIROSEProvider::$instance->packet(new ChatPacket(' [*'.$event->user_name.'*]  : wb',$event->color));
            }
        }
        
    }
}
