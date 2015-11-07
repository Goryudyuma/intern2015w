<?php
namespace Nyaan\Controller;
use Baguette\Response;

/**
 * @package   Nyaan\Controller
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

//login処理
final class login
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
		//もしすでにlogin済みならメインページへ飛ばす
        if ($app->session->get('user_id', ['default' => false])) {
            return new Response\RedirectResponse('/');
        }

        // systemは特殊なユーザーなのでログインできない
		//  にしたってこれはひどい。せめて!==とすべき。
        if (isset($_REQUEST['user'], $_REQUEST['password']) && $_REQUEST['user'] != 'system') {
            $user = trim($_REQUEST['user']);
            $pass = $_REQUEST['password'];

		//改行コードを入れたくなくてこうしてるのかな・・・？
		//まずインジェクションが怖いのでプレースホルダを使う
		//文字列連結しまくるのは速度の上でもよろしくないだろう。一文に持っていく。
		//passwordがhash化されてなさそう・・・？流出事故の際に被害を減らすためにもhash化するべし。減災！
            $query
                = 'SELECT `users`.`id`, `users`.`slug`, `users`.`name` '
                . 'FROM `users` '
                . 'INNER JOIN `user_passwords` '
                . '   ON `users`.`id` = `user_passwords`.`user_id` '
                . "WHERE `users`.`slug` = \"${user}\" "
                . "  AND `user_passwords`.`password` = \"${pass}\" ";
            $stmt = db()->prepare($query);
            $stmt->execute();

		//セッション情報にログインしているかを持たせる。
		//セッションIDをユーザーIDとは別にするべき。じゃないと乗っ取れる。
		//方法がわからないので、調べながらやる。
            if ($login = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $app->session->set('user_id', $login['id']);
                $app->session->set('user_slug', $login['slug']);
                $app->session->set('user_name', $login['name']);
                return new Response\RedirectResponse('/');
            }
        }

		//もしユーザー名とパスワードの組がDB上になければ、userはそのまま、パスワードを空にして返す。
		//nullではなく''にするべき？
		//??が使える。
        return new Response\TwigResponse('login.tpl.html', [
            'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
        ]);
    }
}
