<?php

use Model\Boosterpack_model;
use Model\Comment_model;
use Model\Login_model;
use Model\Post_model;
use Model\User_model;
use System\Libraries\Core;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {

        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_boosterpacks()
    {
        $posts =  Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $posts]);
    }

    public function get_post(int $post_id){

        $post = Post_model::preparation(Post_model::get_one_by_id($post_id),'full_info');
        return $this->response_success(['post' => $post]);
    }


    public function comment(){

        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $post_id = App::get_ci()->input->post('postId');
        $replay_id = App::get_ci()->input->post('replayId');
        $comment_text = App::get_ci()->input->post('commentText');
        if(isset($replay_id)) {
            $parent = Comment_model::get_one_by_id($replay_id);
            $comment = Comment_model::create([
                'user_id' => User_model::get_user()->get_id(),
                'assign_id' => $parent->get_assign_id(),
                'reply_id' => $parent->get_id(),
                'text' => $comment_text,
                'likes' => 0,
            ]);
        } elseif(isset($post_id)) {
            $comment = Comment_model::create([
                'user_id' => User_model::get_user()->get_id(),
                'assign_id' => $post_id,
                'text' => $comment_text,
                'likes' => 0,
            ]);
        }
        return $this->response_success(['comment'=>Comment_model::preparation($comment)]);
    }


    public function login()
    {
        $post = App::get_ci()->input->post();

        $user = Login_model::login($post);

        $user = User_model::preparation($user,'default');

        return $this->response_success(['user' => $user]);
    }


    public function logout()
    {
        Login_model::logout();

        redirect('/', 'refresh');
    }

    public function add_money(){
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules('sum', 'sum', 'required|numeric|greater_than[0]');
        if ($this->form_validation->run() == FALSE)
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_WRONG_PARAMS,
                ['errors' => $this->form_validation->error_array()]
            );
        }
        else
        {
            $sum = (float)App::get_ci()->input->post('sum');
            $user = User_model::get_user();
            $user->add_money($sum);
        }



        //TODO логика добавления денег
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        //TODO логика покупки и открытия бустерпака по алгоритмку профитбанк, как описано в ТЗ
    }


    /**
     *
     * @return object|string|void
     */
    public function like_comment(int $comment_id)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $comment = Comment_model::get_one_by_id($comment_id);
        $comment->increment_likes(User_model::get_user());
        return $this->response_success(['likes' => $comment->get_likes()]);
    }

    /**
     * @param int $post_id
     *
     * @return object|string|void
     */
    public function like_post(int $post_id)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $post = Post_model::get_one_by_id($post_id);
        $post->increment_likes(User_model::get_user());
        return $this->response_success(['likes' => $post->get_likes()]);
    }


    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпак
    }
}
