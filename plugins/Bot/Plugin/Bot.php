<?php

namespace Plugin\Bot;

use Bot\Event\CommandEvent;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Models\Sender;
use Bot\Provider\IIROSE\Packets\SourcePacket;
use Plugin\Master\Master;

class Bot extends PhpPlugin
{
    public function onCommand(CommandEvent $event)
    {
        if (substr($event->sign, 0, 8) == 'Bot:Low:') {
            if (!Master::isMember($event->sender->getUserId())) {
                $event->sender->sendMessage('you are not in member list');
                return;
            }
            if ($event->sign == 'Bot:Low:Cut') {
                $roomid = explode('_]', explode('[_', strtolower($event->input->getArgument('roomid')))[1])[0];
                if ($roomid == null) {
                    $roomid = IIROSEProvider::$instance->getUserInfo($event->sender->getUsername())->room_id;
                }
                switch (strtolower($event->input->getArgument('type'))) {
                    case 'once':
                        IIROSEProvider::$instance->packet(new SourcePacket('!11_' . $roomid));
                        $msg = 'Once';
                        break;

                    case 'all':
                        IIROSEProvider::$instance->packet(new SourcePacket('!12_' . $roomid));
                        $msg = 'All';
                        break;

                    default:
                        $msg = 'Error';
                        break;
                }
                $event->sender->sendMessage('Cut ' . $msg . ' in  [_' . $roomid . '_] ');
            } elseif ($event->sign == 'Bot:Low:Search') {
                $username = explode('*]', explode('[*', strtolower($event->input->getArgument('username')))[1])[0];
                $uList = IIROSEProvider::$instance->getUserInfo($username);
                if ($uList == null) {
                    $event->sender->sendMessage("\n```ruby\n$404 => 'user was not found'\n\ncheck that the user is online \nif you are quite sure ".'$online'." => true\nplease try again in 2 minutes\n```\n",'000');
                    return;
                }
                
                $event->sender->sendMessage("\n```ruby\n".'$Username'." =>   [*" . $uList->username . "*] \n\n".'$RoomID'."   =>   [_" . $uList->room_id ."_] \n\n".'$Online'."   =>  [" . $uList->online . "][mins] \n```\n```markdown\n> if you want to ban this user \n> please use the command \n```\n```ruby\nExec Bot:High:Ban  [*" . $uList->username . "*]  \n```\n",'000');
            }
        } elseif (substr($event->sign, 0, 9) == 'Bot:High:') {
            if (!Master::isMaster($event->sender->getUserId())) {
                $event->sender->sendMessage('you are not my master');
                return;
            }
            if ($event->sign == 'Bot:High:Notice') {
                $roomid = explode('_]', explode('[_', strtolower($event->input->getArgument('roomid')))[1])[0];
                if ($roomid == null) {
                    $roomid = IIROSEProvider::$instance->getUserInfo($event->sender->getUsername())->room_id;
                }
                IIROSEProvider::$instance->packet(new SourcePacket('!!_' . $roomid . '["' . $event->input->getArgument('msg') . '"]'));
                $event->sender->sendMessage('Notice Sent in  [_' . $roomid . '_] ');
            } elseif ($event->sign == 'Bot:High:Ban') {
                $username = explode('*]', explode('[*', strtolower($event->input->getArgument('username')))[1])[0];
                $time = $event->input->getArgument('time');
                $reason = $event->input->getArgument('reason');
                if (!$this->isTime($time)) {
                    return $event->sender->sendMessage('wrong time format');
                }
                if (!$this->isOnline($username)) {
                    return $event->sender->sendMessage('user is not online, please check if the username is entered correctly');
                }
                IIROSEProvider::$instance->packet(new SourcePacket('!44["' . $username . '","' . $time . '","' . $reason . '"]'));
                $event->sender->sendMessage('Ban Sent');
            } elseif ($event->sign == 'Bot:High:DarkRoom') {
                $username = explode('*]', explode('[*', strtolower($event->input->getArgument('username')))[1])[0];
                $time = $event->input->getArgument('time');
                $reason = $event->input->getArgument('reason');
                if (!$this->isTime($time)) {
                    return $event->sender->sendMessage('wrong time format');
                }
                if (!$this->isOnline($username)) {
                    return $event->sender->sendMessage('user is not online, please check if the username is entered correctly');
                }
                IIROSEProvider::$instance->packet(new SourcePacket('!@4["' . $username . '","' . $time . '","' . $reason . '"]'));
                $event->sender->sendMessage('DarkRoom Sent');
            } elseif ($event->sign == 'Bot:High:Move') {
                $username = explode('*]', explode('[*', strtolower($event->input->getArgument('username')))[1])[0];
                $roomid = explode('_]', explode('[_', strtolower($event->input->getArgument('roomid')))[1])[0];
                if (!$this->isOnline($username)) {
                    return $event->sender->sendMessage('user is not online, please check if the username is entered correctly');
                }
                if (strlen($roomid) != 13) {
                    return $event->sender->sendMessage('wrong roomid format');
                }
                echo '!2["' . $username . '","' . $roomid . '"]';
                IIROSEProvider::$instance->packet(new SourcePacket('!2["' . $username . '","' . $roomid . '"]'));
                $event->sender->sendMessage('Move Sent');
            } elseif ($event->sign == 'Bot:High:Mute') {
                $username = explode('*]', explode('[*', strtolower($event->input->getArgument('username')))[1])[0];
                $time = $event->input->getArgument('time');
                $reason = $event->input->getArgument('reason');
                if (!$this->isTime($time)) {
                    return $event->sender->sendMessage('wrong time format');
                }
                if (!$this->isOnline($username)) {
                    return $event->sender->sendMessage('user is not online, please check if the username is entered correctly');
                }
                switch ($event->input->getArgument('type')) {
                    case '1':
                        $type = 1;
                        $tip = 'mute';
                        break;

                    case '2':
                        $type = 2;
                        $tip = 'unplay';
                        break;

                    case '3';
                        $type = 3;
                        $tip = 'unplay and mute';
                        break;

                    default:
                        return $event->sender->sendMessage('wrong type');
                }
                IIROSEProvider::$instance->packet(new SourcePacket('!*4["' . $type . '","' . $username . '","' . $time . '","' . $reason . '"]'));
                $event->sender->sendMessage($tip . ' Sent');
            }
        }
    }
    public function isTime($time)
    {
        if (!in_array(substr($time, -1), ['s', 'm', 'h', 'd', '&'])) {
            return false;
        } else if ($time != '&' && !is_numeric(substr($time, 0, -1))) {
            return false;
        }
        return true;
    }
    public function isOnline($username)
    {
        if (IIROSEProvider::$instance->getUserInfo($username)) {
            return true;
        } else {
            return false;
        }
    }
}
