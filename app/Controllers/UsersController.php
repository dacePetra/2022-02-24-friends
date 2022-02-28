<?php

namespace App\Controllers;

use App\Database;
use App\Models\User;
use App\Redirect;
use App\Views\View;

class UsersController
{
    public function index(): View
    {
        $usersQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('users')
            ->executeQuery()
            ->fetchAllAssociative();
        $userProfilesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('user_profiles')
            ->executeQuery()
            ->fetchAllAssociative();

        $users = [];
        foreach ($usersQuery as $userData) {
            foreach ($userProfilesQuery as $userProfileData) {
               if($userData['id']==$userProfileData['user_id']) {
                    $users [] = new User(
                        $userProfileData['name'],
                        $userProfileData['surname'],
                        $userProfileData['birthday'],
                        $userData['email'],
                        $userData['password'],
                        $userData['created_at'],
                        $userData['id']
                    );
                }
            }
        }
        return new View('Users/index', [
            'users' => $users
        ]);
    }

    public function show(array $vars): View
    {
        $usersQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('users')
            ->where('id = ?')
            ->setParameter(0, (int) $vars['id'])
            ->executeQuery()
            ->fetchAssociative();
        $userProfilesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('user_profiles')
            ->where('user_id = ?')
            ->setParameter(0, (int) $vars['id'])
            ->executeQuery()
            ->fetchAssociative();

        $user= new User(
                        $userProfilesQuery['name'],
                        $userProfilesQuery['surname'],
                        $userProfilesQuery['birthday'],
                        $usersQuery['email'],
                        $usersQuery['password'],
                        $usersQuery['created_at'],
                        $usersQuery['id']
                    );

        return new View('Users/show', [
            'user'=>$user
        ]);
    }

    public function signup(array $vars): View
    {
        return new View('Users/signup');
    }

    public function register(array $vars):Redirect
    {
        if(empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['birthday']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['password_repeat'])){
            // Empty input
            return new Redirect('/users/error');
        }
        if(!preg_match("/^[a-zA-Z]*$/", $_POST['name']) || !preg_match("/^[a-zA-Z]*$/", $_POST['surname'])){
            // Invalid name or surname
            return new Redirect('/users/error');
        }
        if($_POST['password'] != $_POST['password_repeat']){
            // Passwords don't match
            return new Redirect('/users/error');
        }
        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            // Invalid email
            return new Redirect('/users/error');
        }
        $usersQuery = Database::connection()
            ->createQueryBuilder()
            ->select('email')
            ->from('users')
            ->where("email = '{$_POST['email']}'")
            ->executeQuery()
            ->fetchAssociative();
        if($usersQuery!=false){
            // Email taken
            return new Redirect('/users/email');
        }
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        Database::connection()
            ->insert('users', [
                'email' => $_POST['email'],
                'password' => $hashedPassword
            ]);
        $usersQuery = Database::connection()
            ->createQueryBuilder()
            ->select('email, id')
            ->from('users')
            ->where("email = '{$_POST['email']}'")
            ->executeQuery()
            ->fetchAssociative();
        $id = $usersQuery["id"];
        Database::connection()
            ->insert('user_profiles', [
                'user_id' => $id,
                'name' => $_POST['name'],
                'surname' => $_POST['surname'],
                'birthday' => $_POST['birthday']
            ]);
        return new Redirect('/');
    }

    public function error(array $vars): View
    {
        return new View('Users/error');
    }

    public function email(array $vars): View
    {
        return new View('Users/email');
    }

    public function login(array $vars): View
    {
        return new View('Users/login');
    }

    public function enter(array $vars): Redirect
    {
        if(empty($_POST['logemail']) || empty($_POST['logpassword'])){
            // Empty input
            return new Redirect('/users/error');
        }
        $usersQuery = Database::connection()
            ->createQueryBuilder()
            ->select('email, password, created_at, id')
            ->from('users')
            ->where("email = '{$_POST['logemail']}'")
            ->executeQuery()
            ->fetchAssociative();
        if($usersQuery['email']!=$_POST['logemail']){
            // Email not registered
            return new Redirect('/users/login');
        }

        if (!password_verify($_POST['logpassword'], $usersQuery['password'])) {
            // Wrong password
            return new Redirect('/users/login');
        }

        $userProfilesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('user_profiles')
            ->where('user_id = ?')
            ->setParameter(0, (int) $usersQuery["id"])
            ->executeQuery()
            ->fetchAssociative();

        session_start();
        $_SESSION["name"] = $userProfilesQuery['name']." ".$userProfilesQuery['surname'];
        $_SESSION["id"] = $userProfilesQuery['user_id'];
        return new Redirect('/welcome');
    }

    public function logout(): View
    {
        session_start();
        session_unset();
        session_destroy();
        return new View('Users/logout');
    }

}