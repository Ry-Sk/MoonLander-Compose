<?php

namespace Plugin\Morning;

use Bot\Event\ChatEvent;
use Bot\Event\CommandEvent;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\Event\JoinEvent;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Models\Sender;
use Bot\Provider\IIROSE\Packets\ChatPacket;
use Bot\Provider\IIROSE\Packets\BoardCastPacket;
use Console\ErrorFormat;
use GuzzleHttp\Client;

class Morning extends PhpPlugin
{
    
    public $morning=[
        'name' => [],
        'uid' => [],
        'luck' => [],
        'time' => [
            'h' => [],
            'i' => [],
            's' => []
        ],
        'i' => 1
    ];
    public $p=[];
    public function onCommand(CommandEvent $event)
    {
        $luck = ['大吉','上吉','中吉','上上','中平','中下','下下'];
        if ($event->sign == 'morning:zao'){
            try {
                date_default_timezone_set("Asia/Shanghai");
                $h = date('H');
                $name = $event->sender->getUsername();
                $uid = $event->sender->getUserId();
                if($uid == '5e8898aa5af3c') {
                    $name = '明日';
                }
                if(in_array($uid,$this->morning['uid'])) {
                    $msg = " [*$name*]  ： 您已经签到过了哦~ 发送 /rank 查看签到详情";
                } else {
                    if(mb_strlen($name) > 6) {
                        $name = mb_substr($name,0,6).'...';
                    }
                    $l = array_rand($luck);
                    array_push($this->morning['name'],$name);
                    array_push($this->morning['uid'],$uid);
                    array_push($this->morning['luck'],$l);
                    array_push($this->morning['time']['h'],date('H'));
                    array_push($this->morning['time']['i'],date('i'));
                    array_push($this->morning['time']['s'],date('s'));
                    $msg = "\\\\\\( ## #1 ".$this->morning['name'][0].":".$this->morning['uid'][0]."\n\n---\n\n用户名： **$name** \nUID： **$uid**\n排名： **#".$this->morning['i']."**\n时间： **".date('H').":".date('i')."**\n\n**今日运势！ ".$luck[$l]."**\n\n---\n\n*您可以发送 /rank 查看签到详情*";
                    $this->morning['i'] = $this->morning['i']+1;
                } 
                $event->sender->sendRawMessage($msg);
            } catch (\Exception $e) {
                $event->sender->sendRawMessage(' [*'.$event->sender->getUsername().'*]   :  喵呜~喵喵cpu坏啦~喂......不要帮我修啦',"9F353A");
                ErrorFormat::dump($e);
            }
        } elseif ($event->sign == 'morning:rank'){
            try {
                if(count($this->morning['name'])){  
                    $rank = "\\\\\\(|排行&nbsp;|&nbsp;用户名&nbsp;|&nbsp;UID&nbsp;|&nbsp;运势&nbsp;|&nbsp;签到时间|\n|:-|:-|:-|:-:|-:|";
                    for ($i=0; $i < count($this->morning['name']); $i++) { 
                        $r = $i + 1; 
                        $rank = $rank."\n".'|**'.$r.'**|&nbsp;**'.$this->morning['name'][$i].'**&nbsp;|&nbsp;'.$this->morning['uid'][$i].'&nbsp;|&nbsp;**'.$luck[$this->morning['luck'][$i]].'**&nbsp;|&nbsp;**'.$this->morning['time']['h'][$i].':'.$this->morning['time']['i'][$i].':'.$this->morning['time']['s'][$i].'**|';
                    }
                } else $rank = '暂无数据请等待签到开始后执行';
                $event->sender->sendRawMessage($rank);
            } catch (\Exception $e) {
                $event->sender->sendRawMessage(' [*'.$event->sender->getUsername().'*]   :  喵呜~喵喵cpu坏啦~喂......不要帮我修啦',"9F353A");
                ErrorFormat::dump($e);
            }
        }
    }
    public function onChat(ChatEvent $event) 
    {
        $message = $event->getMessage();
        $botname = strtolower($this->bot->username);
        if ((strpos($message, $botname) !== false || strpos($message, '虎子') !== false) && (strpos($message, '早安') !== false || strpos($message, '午安') !== false || strpos($message, '晚安') !== false )) {  
            $luck = ['大吉','上吉','中吉','上上','中平','中下','下下'];
            date_default_timezone_set("Asia/Shanghai");
            $h = date('H');
            $name = $event->getSender()->getUsername();
            $uid = $event->getSender()->getUserId();
            if($uid == '5e8898aa5af3c') {
                $name = '明日';
            }
            if(in_array($uid,$this->morning['uid'])) {
                $msg = " [*$name*]  ： 您已经签到过了哦~ 发送 /rank 查看签到详情";
            } else {
                if(mb_strlen($name) > 6) {
                    $name = mb_substr($name,0,6).'...';
                }
                $l = array_rand($luck);
                array_push($this->morning['name'],$name);
                array_push($this->morning['uid'],$uid);
                array_push($this->morning['luck'],$l);
                array_push($this->morning['time']['h'],date('H'));
                array_push($this->morning['time']['i'],date('i'));
                array_push($this->morning['time']['s'],date('s'));
                $msg = "\\\\\\( ## #1 ".$this->morning['name'][0].":".$this->morning['uid'][0]."\n\n---\n\n用户名： **$name** \nUID： **$uid**\n排名： **#".$this->morning['i']."**\n时间： **".date('H').":".date('i')."**\n\n**今日运势！ ".$luck[$l]."**\n\n---\n\n*您可以发送 /rank 查看签到详情*";
                $this->morning['i'] = $this->morning['i']+1;
            } 
            $event->getSender()->sendRawMessage($msg);
        }
    }
}