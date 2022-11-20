<?php

namespace Plugin\Random;

use Bot\Event\CommandEvent;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\Event\JoinEvent;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Models\Sender;
use Bot\Provider\IIROSE\Packets\ChatPacket;
use Bot\Provider\IIROSE\Packets\BoardCastPacket;
use Console\ErrorFormat;
use GuzzleHttp\Client;

class Random extends PhpPlugin
{
    public function onCommand(CommandEvent $event)
    {
        if ($event->sign == 'random:hitokoto'){
            try {
                $client = new Client();
                $response = $client->get('https://v1.hitokoto.cn/?encode=json&c=a&c=b&c=d&c=e&c=j&c=f');
                $data=$response->getBody()->getContents();
                $result = json_decode($data, true);
                $event->output->writeln("|||\n|:-|-:|\n|**".$event->sender->getUsername()."：**||\n|&nbsp;|&nbsp;|\n| ".$result['hitokoto'] . "||\n|&nbsp;|&nbsp;|\n||来自 **".$result['from'] . '**|');
            } catch (\Exception $e) {
                $event->sender->sendRawMessage('\\\\\( **警告：** 出现了一些未知错误，但请不要修复我！','B54434');
                ErrorFormat::dump($e);
            }
         }elseif ($event->sign == 'random:nicewords'){
            try {
                $client = new Client();
                $type = $event->input->getArgument('type');
                if ($type == "绿茶" || $type == "綠茶" || $type == "婊子" || $type == "f" || $type == "F") {
                    $response = $client->get('https://api.lovelive.tools/api/SweetNothings/2/Serialization/Json?genderType=F');
                } elseif ( $type == "渣男" || $type == "海王" || $type == "m" || $type == "M") {
                    $response = $client->get('https://api.lovelive.tools/api/SweetNothings/2/Serialization/Json?genderType=M');
                } elseif ( $type == "全部" || $type == "all" || $type == "ALL") { 
                    $response = $client->get('https://api.lovelive.tools/api/SweetNothings/2/Serialization/Json');
                } else {
                    $event->sender->sendRawMessage(' [*'.$event->sender->getUsername().'*]   :   Please don\'t try the command! Unless you want to be killed by [*Saiki*] (laughs)');
                    return;
                }
                $data=$response->getBody()->getContents();
                $result = json_decode($data, true);
                $event->output->writeln("|||\n|:-|-:|\n|&nbsp;|&nbsp;|\n|" . $result['returnObj'][0]."||\n|&nbsp;|&nbsp;|\n||**".$event->sender->getUsername().'**|');
                 //$event->sender->sendRawMessage(' [*'.$event->sender->getUsername().'*]   :   提供API的那个网站已经拉闸了（请等待恢复');
            } catch (\Exception $e) {
                $event->sender->sendRawMessage(' [*'.$event->sender->getUsername().'*]   :  The website that provides the API has been closed (please wait for the restoration)',"9F353A");
                ErrorFormat::dump($e);
            }
        }elseif ($event->sign=='random:number') {
            try {
                $all = strtoupper($event->input->getArgument('exp'));
                $parms = explode('+', $all);
                $o = 0;
                foreach ($parms as $parm) {
                    $pparms = explode('D', $parm);
                    if ($pparms[0] > 100) {
                        return 0;
                    }
                    for ($i = 0; $i < $pparms[0]; $i++) {
                        $r = $this->bcrandom(1, $pparms[1]);
                        $o = bcadd($o, $r);
                    }
                }
                $event->sender->sendRawMessage(' [*'.$event->sender->getUsername().'*]   :  ' . $o);
            } catch (\Throwable $e) {
                ErrorFormat::dump($e);
                $event->output->write('Expression error');
            }
        }elseif ($event->sign=='random:jitang') {
            try {
                $client = new Client();
                $response = $client->post('https://www.emojidaquan.com/Others/new_ex', [
                    'form_params' => [
                        'cate' => '2'
                    ]
                ]);
                $data=$response->getBody()->getContents();
                $result = json_decode($data, true);
                $event->output->writeln("|||\n|:-|-:|\n|&nbsp;|&nbsp;|\n|".$result['content']."||\n|".$result['emoji']."||\n|&nbsp;|&nbsp;|\n||**".$event->sender->getUsername().'**|');
            } catch (\Exception $e) {
                $event->sender->sendRawMessage('\\\\\( **警告：** 出现了一些未知错误，但请不要修复我！','B54434');
                ErrorFormat::dump($e);
            }
        }


    }
    private function bcrandom($p1, $p2)
    {
        if ($p1==$p2) {
            return $p1;
        }
        if (bccomp($p1, $p2)==1) {
            $min=$p2;
            $max=$p1;
        } else {
            $min=$p1;
            $max=$p2;
        }
        $total=bcsub($max, $min);
        $needLength=strlen($total);
        $randomFile=fopen('/dev/urandom', 'r');
        $randomMax=bcsub(bcpow(256, $needLength), 1);
        //$randomMax=bcsub(bcpow(256,1),1);
        $noAbove=bcsub($randomMax, bcmod($randomMax, $total));
        //var_dump($randomMax);
        while (true) {
            $salt=$this->hex2int(bin2hex(fread($randomFile, $needLength)));
            //$salt=hex2int(bin2hex(fread($randomFile,1)));
            if (bccomp($salt, $noAbove)!=1) {
                return bcadd($min, bcmod($salt, bcadd($total, 1)));
            }
        }
    }
    private function hex2int($hex)
    {
        $len = strlen($hex);
        $dec =0;
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }
}