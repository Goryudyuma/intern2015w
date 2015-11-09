<?php
namespace Nyaan\Controller;
use Nyaan\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

//各roomでの動作を書いている
final class room
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
		//roomの名前を取ってくる
        $room  = $action->param['slug'];

		//プレースホルダ！！！
		//完全一致検索なので=でもよい。
        $query = "SELECT * FROM `rooms` WHERE `slug` = \"{$room}\"";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

		//messageがあれば受理し入れる。
        if (!empty($_REQUEST['message'])) {
			//挿入した瞬間に時間を入れるべき
            $now = date('Y-m-d H:i:s', strtotime('+9 hours'));
			//replaceこれだけでは、HTMLやjavascriptが入ってしまい、XSSが容易に行えそう
            $message = str_replace('"', '\\"', $_REQUEST['message']);
            $user_id = $_REQUEST['user_id'];

			//プレースホルダ！！
            $query = "INSERT INTO `posts` VALUES( {$data['id']}, {$user_id}, \"{$now}\", \"{$message}\" )";
            $stmt = db()->prepare($query);
            $stmt->execute();
        }

		//プレースホルダ！！！！
        $query = "SELECT * FROM `posts` WHERE `room_id` = {$data['id']} ORDER BY datetime(`posted_at`) DESC LIMIT 100";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $talk = $stmt->fetchALL(\PDO::FETCH_ASSOC);

		//書き込んだ人全員のユーザー情報を持ってきている。
		//最大で100回クエリが飛ぶことになるので、改善できるならしたほうが良さそう。
        $users = [];
        foreach ($talk as $s) {
            $user_id = $s['user_id'];
            if (empty($users[$user_id])) {
                $query = "SELECT * FROM `users` WHERE `id` = {$user_id}";
                $stmt = db()->prepare($query);
                $stmt->execute();
                $users[$user_id] = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        return new Response\TemplateResponse('room.tpl.html', [
            'slug' => $room,
            'room' => $data,
            'talk' => $talk,
            'users' => $users,
        ]);
    }
}
