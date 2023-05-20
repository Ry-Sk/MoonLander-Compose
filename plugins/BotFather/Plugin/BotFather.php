<?php


namespace Plugin\BotFather;

use Bot\Event\ChatEvent;
use Bot\Event\CommandEvent;
use Bot\Models\Plugin;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\IIROSEProvider;
use GuzzleHttp\Client;
use Models\Bot;
use Models\BotPlugin;

class BotFather extends PhpPlugin
{
    public function onCommand(CommandEvent $event)
    {
        if($event->sign=='botFather:login'){
            $username=$event->input->getArgument('username');
            $password=$event->input->getArgument('password');
            $client = new Client();
            $response = $client->post(
                'https://a.iirose.com/lib/php/system/login_member_ajax.php',
                [
                    'form_params'=>[
                        'n'=>$username,
                        'p'=>md5($password),
                    ]
                ]
            );
            if ($response
                && $response->getBody()) {
                $content=$response->getBody()->getContents();
                if (strlen($content)==13) {
                    $uid=$content;
                    /** @var Bot $bot */
                    $bot= Bot::where('uid', '=', $uid)->first();
                    if (!$bot) {
                        $bot=new Bot();
                        $bot->uid=$uid;
                        $bot->username=$username;
                        $bot->password=$password;
                        $bot->token=uniqid('login');
                    }
                    $bot->enable=2;
                    $bot->room=IIROSEProvider::$instance->getUserInfo($event->sender->getUsername())->room_id;
                    $bot->saveOrFail();
                    $botPlugin=BotPlugin::where('bot_id',$bot->id)->where('slug','BotFather')->first();
                    if(!$botPlugin){
                        $botPlugin=new BotPlugin();
                    }
                    $botPlugin->bot_id=$bot->id;
                    $botPlugin->slug='BotFather';
                    $botPlugin->configure=json_encode(['master'=>$event->sender->getUserId()]);
                    $botPlugin->save();

                    $botPlugin=BotPlugin::where('bot_id',$bot->id)->where('slug','Master')->first();
                    if(!$botPlugin){
                        $botPlugin=new BotPlugin();
                    }
                    $botPlugin->bot_id=$bot->id;
                    $botPlugin->slug='Master';
                    $botPlugin->configure=json_encode(['master'=>$event->sender->getUserId(),'member'=>[],'follow'=>0]);
                    $botPlugin->save();

                    $event->sender->sendMessage('success');
                    return;
                } else {
                    $event->sender->sendMessage("Password error");
                    return;
                }
            }
            $event->sender->sendMessage("Unable to connect to API");
        }
        if($event->sign=='botFather:signout'){
            $username=$event->input->getArgument('username');
            $password=$event->input->getArgument('password');
            $client = new Client();
            $response = $client->post(
                'https://a.iirose.com/lib/php/system/login_member_ajax.php',
                [
                    'form_params'=>[
                        'n'=>$username,
                        'p'=>md5($password),
                    ]
                ]
            );
            if ($response
                && $response->getBody()) {
                $content=$response->getBody()->getContents();
                if (strlen($content)==13) {
                    $uid=$content;
                    /** @var Bot $bot */
                    $bot= Bot::where('uid', '=', $uid)->first();
                    if (!$bot) {
                        $bot=new Bot();
                        $bot->uid=$uid;
                        $bot->username=$username;
                        $bot->password=$password;
                        $bot->token=uniqid('login');
                    }
                    $bot->enable=0;
                    $bot->room=IIROSEProvider::$instance->getUserInfo($event->sender->getUsername())->room_id;
                    $bot->saveOrFail();
                    $botPlugin=BotPlugin::where('bot_id',$bot->id)->where('slug','BotFather')->first();
                    if(!$botPlugin){
                        $botPlugin=new BotPlugin();
                    }
                    $botPlugin->bot_id=$bot->id;
                    $botPlugin->slug='BotFather';
                    $botPlugin->configure=json_encode(['master'=>$event->sender->getUserId()]);
                    $botPlugin->save();
                    $event->sender->sendMessage('success');
                    return;
                } else {
                    $event->sender->sendMessage("Password error");
                    return;
                }
            }
            $event->sender->sendMessage("Unable to connect to API");
        }
        if(substr($event->sign,0,10)=='botFather:'){
            if($event->sender->getUserId()!=$this->config['master']){
                $event->sender->sendMessage('Oooooooooo, you are not my master');
                return;
            }
        }
       /*  if($event->sign=='botFather:room'){
            $roomid = explode('_]',explode('[_',strtolower($event->input->getArgument('roomid')))[1])[0];
            if(strlen($roomid) == 13) {
                $event->sender->sendMessage('The transmission order has been received!！','A5A051');
                sleep(5);
                Bot::$instance->setRoom($roomid);
            } else $event->sender->sendMessage('Format Err','A5A051');
            return;
        }
        if($event->sign=='botFather:here'){
            $event->sender->sendMessage('The transmission order has been received!','A5A051');
            Bot::$instance->setRoom(IIROSEProvider::$instance->getUserInfo($event->sender->getUsername())->room_id);
            return;
        } */
        if($event->sign=='botFather:enablePlugin'){
            $pluginName=$event->input->getArgument('plugin');
            $plugin=Plugin::find($pluginName);
            /** @var BotPlugin botPlugin */
            $botPlugin=BotPlugin::where('bot_id',$this->bot->id)->where('slug',$pluginName)->first();
            if($botPlugin){
                $event->sender->sendMessage('The plugin is already running');
            }else{
                $botPlugin=new BotPlugin();
                $botPlugin->bot_id=$this->bot->id;
                $botPlugin->slug=$pluginName;
                $botPlugin->configure=json_encode($plugin->getDefaultConfig());
                $botPlugin->save();
                $event->sender->sendMessage('It\'s done!');
            }
        }
        if($event->sign=='botFather:disablePlugin'){
            $pluginName=$event->input->getArgument('plugin');
            $plugin=Plugin::find($pluginName);
            /** @var BotPlugin botPlugin */
            $botPlugin=BotPlugin::where('bot_id',$this->bot->id)->where('slug',$pluginName)->first();
            if($botPlugin){
                $botPlugin->delete();
                $event->sender->sendMessage('Plugin is disabled');
            }else{
                $event->sender->sendMessage('No enabled plugins found');
            }
        }
        if($event->sign=='botFather:listPlugin'){
            /** @var BotPlugin[] botPlugin */
            $botPlugins=BotPlugin::where('bot_id',$this->bot->id)->get();
            $op='';
            foreach($botPlugins as $botPlugin){
                $op.=$botPlugin->slug."\n";
            }
            $event->sender->sendMessage($op);
        }
    }
}