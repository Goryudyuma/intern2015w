<?php
namespace Nyaan\Controller;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

//部屋を追加するクラス
final class add_room
{
	function action(\Baguette\Application $app, \Teto\Routing\Action $action)
	{
		//setされていない時はすぐに返してよし
		//変数名もっとまともなのに変える。isOverlapRet? あとキャメルケースで統一したい。
		$is_daburi = self::isTyouhuku(isset($_REQUEST['slug']) ?? '');

		//条件と関数は分ける！registは必ずtrueがかえってくるので、if文の条件式ではなくif文内で行う
		if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['name'])
				&& self::regist($_REQUEST['slug'], $_REQUEST['name'], $app->getLoginUser())
		   ) {
			return new Response\RedirectResponse('/rooms/' . $_REQUEST['slug']);
		}

		return new Response\RedirectResponse('/');
	}

	//関数名がダサいので変えたい。せめてisOverlap?
	private static function isTyouhuku(string $slug): bool
	{
		//プレースホルダ使う。インジェクションが怖い。
		//ここはあるかどうかを確かめたいだけなので、EXISTSを使ってもいいかも？
		$query = "SELECT * FROM `rooms` WHERE `slug` = \"${slug}\" ";
		$stmt = db()->prepare($query);
		$stmt->execute();
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);

		//ここ、反転して返して返した先で更に反転しているので、なくしたほうがいい。
		//それに伴い関数名を変更するべし。
		return !empty($data);
	}

	//registという英語はない上に、別でregistという名前のファイルやクラスまであるので、同名は(メンテナンス上)やめたほうが良い。
	private static function regist($slug, $name, $user): bool
	{
		//プレースホルダ！！！
		//ACIDを壊さないようにトランザクション使うべき？
		$query = "INSERT INTO `rooms`(`slug`, `name`) VALUES( \"{$slug}\", \"{$name}\" ); ";
		$stmt = db()->prepare($query);
		$stmt->execute();
		$id = db()->lastInsertId();

		//ここ、SQLiteのタイムスタンプ使うべきかも？->要テーブル構造変更
		
		//$now = date('Y-m-d H:i:s', strtotime('+9 hours'));
		$user_name = $user->name;
		$message = str_replace('"', '\\"', "**{$user_name}さん**が部屋を作りました！");
		//$query = "INSERT INTO `posts` VALUES( {$id}, 0, \"{$now}\", \"{$message}\" )";
		$query = "INSERT INTO `posts`( `room_id`, `user_id`, `message`) VALUES( {$id}, 0, \"{$message}\" )";
		$stmt = db()->prepare($query);
		$stmt->execute();

		//trueを返す必要はあまりない・・・
		return true;
	}
}
