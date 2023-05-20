<?php

namespace Plugin\Master;

use Bot\Event\CommandEvent;
use Bot\Models\Plugin;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\Packets\ChatPacket;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Event\GotoEvent;
use Bot\Provider\IIROSE\Packets\SourcePacket;
use Bot\Provider\IIROSE\Packets\PersonChatPacket;
use Models\Bot;
use Models\BotPlugin;


class Master extends PhpPlugin
{
    public function onCommand(CommandEvent $event) {
        if(substr($event->sign,0,7)=='Master:'){
            $userUid=$event->sender->getUserId();
            if($this->config['master']!=$userUid && !array_search($userUid,$this->config['member'],true)){
                $event->sender->sendMessage('you are not my master');
                return;
            }
            /* $event->sender->sendMessage('pass!'); */
            switch (substr($event->sign,7)) {
                case 'addMember':
                    $addUid= explode('@]',explode('[@',strtolower($event->input->getArgument('uid')))[1])[0];
                    if(strlen($addUid) != 13) {
                        $event->sender->sendMessage('uid error~');
                        break;
                    }
                    if($this->config['master']==$userUid){
                        if ($this->config['master']==$addUid || in_array($addUid,$this->config['member'],true)) {
                            $event->sender->sendMessage('dupplicat uid !');
                            break;
                        }
                        $this->config['member'][]=$addUid;
                        $event->sender->sendMessage('add successful !');
                    }else{
                        $event->sender->sendMessage('master required !');
                    }
                    break;

                case 'follow':
                    if ($this->config['follow'] == 0) {
                        $this->config['follow'] = 1;
                    } else {
                        $this->config['follow'] = 0;
                    }
                    $event->sender->sendMessage('Follow toogle ' . ($this->config['follow'] == 0 ? 'off' : 'on'));
                    break;

                case 'here':
                    $room=IIROSEProvider::$goTo=IIROSEProvider::$instance->getUserInfo($event->sender->getUsername())->room_id;
                    $RoomPsd=json_decode(file_get_contents('masterConfig/RoomPsd_'.Bot::$instance->uid.'.json'),true);
                    $rp=isset($RoomPsd[$room])?$RoomPsd[$room]:'';
                    IIROSEProvider::$instance->packet(new SourcePacket('m'.$room.'>'.$rp));
                    break;

                case 'roomPwd':
                    $roomPsd=json_decode(file_get_contents('masterConfig/RoomPsd_'.Bot::$instance->uid.'.json'),true);
                    $rid=$event->input->getArgument('rid');
                    if($rid===null){
                        $str='\\\\\\\(| Room | Passwd |'."\n".'|:-|-:|';
                        foreach ($roomPsd as $rid => $pwd) {
                            $str.="\n| [_".$rid.'_] | **'.$pwd.'** |';
                        }
                        $event->sender->sendMessage('list sent by private chat');
                        IIROSEProvider::$instance->packet(new PersonChatPacket($event->sender->getUserId(),substr($str,1),''));
                        break;
                    }
                    $rid=explode('_]',explode('[_',strtolower($rid))[1])[0];
                    if(strlen($rid)!=13){
                        $event->sender->sendMessage('format error !');
                        break;
                    }
                    $pwd=$event->input->getArgument('pwd');
                    if($pwd===null){
                        unset($roomPsd[$rid]);
                        $event->sender->sendMessage('remove successful !');
                    }else{
                        if(isset($roomPsd[$rid]) && $roomPsd[$rid]===$pwd){
                            $event->sender->sendMessage('room already exists !');
                            break;
                        }else{
                            $roomPsd[$rid]=$pwd;
                            $event->sender->sendMessage(isset($roomPsd[$rid])?'room password modified !':'add successful !');
                        }
                    }
                    file_put_contents('conf.d/Master/RoomPsd_'.Bot::$instance->uid.'.json',json_encode($roomPsd));
                    break;

                default:
                    $event->sender->sendMessage('sign error !');
                    break;
            }
            $bot= Bot::where('uid', '=', $this->bot->uid)->first();
            $botPlugin=BotPlugin::where('bot_id',$bot->id)->where('slug','Master')->first();
            $botPlugin->bot_id=$bot->id;
            $botPlugin->slug='Master';
            $botPlugin->configure=json_encode($this->config);
            $botPlugin->save();
        }
    }
    public function onGoto(GotoEvent $event) {
        if($event->user_id == $this->config['master'] && $this->config['follow'] == 1) {
            $room=IIROSEProvider::$goTo=$event->to;
            $RoomPsd=json_decode(file_get_contents('masterConfig/RoomPsd_'.Bot::$instance->uid.'.json'),true);
            $rp=isset($RoomPsd[$room])?$RoomPsd[$room]:'';
            IIROSEProvider::$instance->packet(new SourcePacket('m'.$room.'>'.$rp));
        }
    }

    public static function isMaster($id){
        if($GLOBALS[Bot::$instance->uid]['config']['Master']['master'] == $id){
            return true;
        }else{
            return false;
        }
    }

    public static function isMember($id){
        if($GLOBALS[Bot::$instance->uid]['config']['Master']['master'] == $id){
            return true;
        }else{
            foreach($GLOBALS[Bot::$instance->uid]['config']['Master']['member'] as $member){
                if($member == $id){
                    return true;
                }
            }
            return false;
        }
    }
}