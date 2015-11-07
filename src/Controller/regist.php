<?php
namespace Nyaan\Controller;
use Baguette\Response;

// FIXME: そんな英語はない
//  とりあえずsignupとでもしたい・・・
//  あと、ここへアクセスする手段がないので、メインページに作る。
class regist
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
		//既にログインしていればメインページに飛ばす
        if ($app->session->get('user_id', ['default' => false])) {
            return new Response\RedirectResponse('/');
        }

		//またここにもローマ字関数名・・・
		//??にはissetが含まれているのでissetはいらない。
		//未定義なら空文字列にしないでダメだって返すほうがよい
		//issetのせいでboolで返っている！何入れても通しやがる糞コード・・・使い方間違ってます・・・
        $is_daburi = self::isTyouhuku(isset($_REQUEST['user']) ?? '');

        if (!$is_daburi && isset($_REQUEST['slug'], $_REQUEST['password'])) {
			//返り値からもらわなくてももう持ってる。
            $login = self::regist($_REQUEST['slug'], $_REQUEST['user'], $_REQUEST['password']);
            $app->session->set('user_id', $login['id']);
            $app->session->set('user_slug', $login['slug']);
            $app->session->set('user_name', $login['name']);

            return new Response\RedirectResponse('/');
        }

		//ダブっていても何も返してくれない。
		//それどころかそのユーザーでログインできてしまう。
        return new Response\TwigResponse('regist.tpl.html', [
            'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
            'is_daburi' => $is_daburi,
        ]);
    }

    private static function isTyouhuku(string $user_name): bool
    {
        // systemは特殊なユーザーなので登録できない
        if (empty($user_name) || $user_name === 'system') {
            return false;
        }

		//プレースホルダ！！
        $user = trim($user_name);

		//この$passどこで使うの？？
        $pass = $_REQUEST['password'];
        $query = "SELECT * FROM `users` WHERE `slug` = \"${user}\" ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $password): array
    {
		//プレースホルダ！！
        $query = "INSERT INTO `users`(`slug`, `name`) VALUES( \"{$slug}\", \"{$name}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();

		//プレースホルダ！！hash化！！！
		//あと、この2つのテーブル分ける意味ない気がする。
        $id = db()->lastInsertId();
        $query = "INSERT INTO `user_passwords` VALUES( {$id}, \"{$password}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
