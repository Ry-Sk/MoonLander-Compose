<?php


namespace Plugin\NetEase;


use Bot\Event\CommandEvent;
use Bot\PluginLoader\PhpPlugin\PhpPlugin;
use Bot\Provider\IIROSE\Event\JoinEvent;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Models\Sender;
use Bot\Provider\IIROSE\Packets\ChatPacket;
use Bot\Provider\IIROSE\Packets\SourcePacket;
use Console\ErrorFormat;
use GuzzleHttp\Client;
use Mhor\MediaInfo\MediaInfo;
use Metowolf\Meting;

class NetEase extends PhpPlugin
{
    public function onCommand(CommandEvent $event)
    {
        $api = new Meting('netease');
        // put your cookies
        $api->cookie("// put your cookies");
        if ($event->sign == 'netease:search') {
            try {
                $keyword = $event->input->getArgument('name');
                $option['limit'] = $event->input->getArgument('limit');
                $search = json_decode($api->format(true)->search($keyword,$option), true);
                $event->output->writeln('**'.$event->sender->getUsername().'** : 搜索到 ' . count($search) . ' 个结果');
                $event->output->writeln(' ');
                $event->output->writeln('| ID | 信息 |');
                $event->output->writeln('|:-|-:|');
                foreach ($search as $result) {
                    $rs = '**'.$keyword.'**';
                    $artist = null;
                    foreach ( $result['artist'] as $per ) {
                        $artist = $artist . '/' . $per;
                    }
                    $artist = substr($artist,1);
                    $event->output->writeln('| '.$result['id'] . ' |' . str_replace($keyword,$rs,$result['name']) . ' - ' . str_replace($keyword,$rs,$artist).'|');
                } 
                $event->output->writeln(' ');
                $event->output->writeln('使用 **/netease:play id** 来点播歌曲');
            } catch (\Exception $e) {
                
                $event->sender->sendRawMessage('\\\\\( **警告：** 出现了一些未知错误，但请不要修复我！','B54434');
                ErrorFormat::dump($e);
            }
        }elseif ($event->sign == 'netease:searchplaylists') {
            try {
                $keyword = $event->input->getArgument('name');
                $option['limit'] = $event->input->getArgument('limit');
                $option['type'] = 1000;
                $search = json_decode($api->search($keyword,$option), true)['result']['playlists'];
                $event->output->writeln('**'.$event->sender->getUsername().'** : 搜索到 ' . count($search) . ' 个结果');
                $event->output->writeln(' ');
                $event->output->writeln('| ID | 信息 |');
                $event->output->writeln('|:-|-:|');
                foreach ($search as $result) {
                    $rs = '**'.$keyword.'**';
                    $description = null;
                    if($result['description']){
                        $description = ' / '.mb_substr($result['description'],0,20).'...';
                    }
                    $event->output->writeln('| '.$result['id'] . ' | ' . str_replace(PHP_EOL,'', str_replace($keyword,$rs,mb_substr($result['name'],0,10)) . '...' . str_replace($keyword,$rs,$description)) .'|');
                } 
                $event->output->writeln(' ');
                $event->output->writeln('使用 **/netease:playlist id** 来点播歌曲');
            } catch (\Exception $e) {
                
                $event->sender->sendRawMessage('\\\\\( **警告：** 出现了一些未知错误，但请不要修复我！','B54434');
                ErrorFormat::dump($e);
            }
        }elseif ($event->sign == 'netease:playlist'){
            try {   
                $id = $event->input->getArgument('id');
                $username = $event->sender->getUsername();
                $option['type'] = 1000;
                if(!is_numeric($id)) {
                    $id = json_decode($api->search($id,$option), true)['result']['playlists'][0]['id'];
                }
                if(empty($id)) {
                    $event->sender->sendRawMessage('\\\\\( **警告：** 未找到歌单！','B54434');
                    return;
                }
                $pdata = json_decode($api->playlist($id), true)['playlist'];
                if($pdata['description']) {
                   $pdes = mb_substr($pdata['description'],0,200)."...\n\n";
                } else $pdes = '';
                $event->sender->sendRawMessage('\\\\\\(' ."[![](".$pdata['coverImgUrl']."?param=600y600)](https://music.163.com/#/playlist?id=".$id.") \n\n \n ## ".$pdata['name']. "\n".$pdes."\n---\n*点击图片即可跳转到歌单详情*");
                $type = $event->input->getArgument('type');
                $bit = $event->input->getArgument('bit');
                $un = $event->sender->getUsername();
                if($type == 0){
                    $mediaInfo = new MediaInfo();
                    /* $um = ''; */
                    $em = 0;
                    foreach ($pdata['trackIds'] as $songs) {
		                $info = json_decode($api->format(true)->song($songs['id']),true)[0];
		                $pic = json_decode($api->format(true)->pic($info['pic_id']),true);
                        $murl = json_decode($api->format(true)->url($songs['id'],128),true)['url'];
                        if(preg_match('/200/',get_headers($murl,1)[0])) {
                            $media = json_encode($mediaInfo->getInfo($murl));
                        } else {
                            $em = $em + 1;
                            continue;
                        } 
                        // $um = $um.htmlspecialchars('&1'.json_encode(['s'=> 's://api.baka.cafe/music/163.php?id='.$songs['id'].'&bit='.$bit.'#ext=.m4a','d'=>json_decode($media, true)['audios'][0]['duration']['milliseconds']/1000,'c'=>substr($pic['url'],4),'n'=>$info['name'],'r'=>implode(' / ',$info['artist']).' @'.$un,'b'=>'@0','o'=>'://music.163.com/#/song?id='.$songs['id']])).'"';
                        IIROSEProvider::$instance->packet(new SourcePacket('&1'.json_encode(['s'=> 's://api.baka.cafe/music/163.php?id='.$songs['id'].'&bit='.$bit.'#ext=.m4a','d'=>json_decode($media, true)['audios'][0]['duration']['milliseconds']/1000,'c'=>substr($pic['url'],4),'n'=>$info['name'],'r'=>implode(' / ',$info['artist']).' @'.$un,'b'=>'@0','o'=>'://music.163.com/#/song?id='.$songs['id']])));
                    }
                    // IIROSEProvider::$instance->packet(new SourcePacket('x'.rtrim($um,'"')));
                    $event->sender->sendRawMessage(' [*'.$this->bot->username . '*]  : 点播完成，共计'.count($pdata['trackIds']).'首歌曲'.$em=$em?"，其中 $em 首点播失败":'');
                }elseif ($type == 1){
                    $event->sender->sendRawMessage('正在发送整只歌单的资料卡格式');
                    foreach ($pdata['trackIds'] as $songs) {
		                $info = json_decode($api->format(true)->song($songs['id']),true)[0];
		                $pic = json_decode($api->format(true)->pic($info['pic_id']),true);
                        $event->output->writeln(str_replace(' ','%20','https://api.baka.cafe/music/163.php?id='.$songs['id'].'&bit='.$bit.'#ext=.flac'.'*#'.$pic['url'].'@|'.$info['name'].'@|'.implode(' / ',$info['artist']).'#*'));
                    }
                }else $event->sender->sendRawMessage('Command error');
            } catch (\Exception $e) {
                $event->sender->sendRawMessage('\\\\\( **出现了一些问题：** 遇到了没有版权的歌曲，已终止点播','B54434');
                ErrorFormat::dump($e);
            }
        }elseif ($event->sign == 'netease:play'){
            try {
                /* $loop = '1'; */
                $id = $event->input->getArgument('id');
                $type = $event->input->getArgument('type');
                $loop = $event->input->getArgument('loop');
                $bit = $event->input->getArgument('bit');
                $un = $event->sender->getUsername();
                if(!is_numeric($id)) {
                    $id = json_decode($api->format(true)->search($id), true)[0]['id'];
                }
                if($type == 1){
                    $mediaInfo = new MediaInfo();
                    $url = json_decode($api->format(true)->url($id,$bit),true)['url'];
                    /* $event->sender->sendRawMessage($url); */
                    $media = json_encode($mediaInfo->getInfo($url));
                    $result = json_decode($api->format(true)->song($id), true)[0];
                    $getSongs = json_decode($api->format(true)->url($id,$bit),true);
                    $artist = null;
                    foreach ( $result['artist'] as $per ) {
                        $artist = $artist . '/' . $per;
                    }
                    $artist = substr($artist,1);
                    if ( $loop > 1000) {
                        $event->sender->sendRawMessage('https://i0.hdslb.com/bfs/album/8e73a9444bbe206344b6412bbf260e2792b890cd.png#e');
                        $event->sender->sendRawMessage('如果不想被 [*Ruby*] 杀掉的话，一首歌曲是不能点太多的哦！');
                        $loop = '1';
                        
                    }
                    for ($i=0; $i < $loop; $i++) { 
                        IIROSEProvider::$instance->packet(new SourcePacket('&1'.json_encode(['s'=> 's://api.baka.cafe/music/163.php?id='.$id.'&bit='.$bit.'#ext=.flac','d'=>json_decode($media, true)['audios'][0]['duration']['milliseconds']/1000,'c'=>substr(json_decode($api->format(true)->pic($result['pic_id']),true)['url'],4),'n'=>$result['name'],'r'=>$artist.' @'.$un,'b'=>'@0','o'=>'://music.163.com/#/song?id='.$id])));
                        //$event->sender->sendRawMessage('&1'.json_encode(['s'=> substr($url, 4) ,'d'=>json_decode($media, true)['audios'][0]['duration']['milliseconds']/1000,'c'=>substr(json_decode($api->format(true)->pic($result['pic_id']),true)['url'],4),'n'=>$result['name'],'r'=>$artist,'b'=>'@0','o'=>'://music.163.com/#/song?id='.$id]));
                        //IIROSEProvider::$instance->packet(new SourcePacket('&1'.json_encode(['s'=> 's'.substr($url, 4),'d'=>json_decode($media, true)['audios'][0]['duration']['milliseconds']/1000,'c'=>substr(json_decode($api->format(true)->pic($result['pic_id']),true)['url'],4),'n'=>$result['name'],'r'=>$artist,'b'=>'@0','o'=>'://music.163.com/#/song?id='.$id])));
                    }
                    $event->output->writeln('**'.$this->bot->username . ':** 已添加歌曲 **[ ' . $artist . ' - ' . $result['name'] .' ]** (' . $getSongs['br'] . 'Kbps) x ' . $loop);
                    
                }elseif($type == 2) {
                    $event->sender->sendRawMessage('\\\\\( **Tips:** 自定义歌曲格式已经生成，请复制下面的那张挂掉的图片链接');
                    $event->sender->sendRawMessage('https://i0.hdslb.com/bfs/album/6d3bdf48e0595163ecd461f5d40a8dee7d7a05c3.png#e');
                    $event->sender->sendRawMessage(file_get_contents('https://api.baka.cafe/music/163.php?id='.$id.'&rg=1&type=3&bit='.$bit));
                    if($loop !== 1){
                        $event->sender->sendRawMessage('\\\\\( **出现了一些问题：** 这个模式下是不可以使用 **LOOP** 参数的哦','B54434');
                    }
                }
            } catch (\Exception $e) {
                $event->sender->sendRawMessage('\\\\\( **出现了一些问题：** 好像这首歌在 **网易云音乐** 上暂时没有版权','B54434');
                ErrorFormat::dump($e);
            }
        }
    }
}
