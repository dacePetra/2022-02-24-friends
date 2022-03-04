<?php

namespace App\Controllers;

use App\Database;
use App\Models\User;
use App\Redirect;
use App\Views\View;

class FriendsController
{
    public function invite(array $vars): Redirect
    {
        $activeId = (int) $_SESSION["id"];
        //check if not friend already
        //check if not himself even the page does not show himself
        $invitee = (int)$vars['id'];
        Database::connection()
            ->insert('friends', [
                'user_id' => $invitee,
                'friend_id' => $activeId
            ]);
        return new Redirect('/users');
    }

    public function accept(array $vars): Redirect
    {
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        $acceptedID = (int)$vars['id'];
        Database::connection()
            ->insert('friends', [
                'user_id' => $acceptedID,
                'friend_id' => $activeId
            ]);
        return new Redirect('/invites');

    }

    public function invites(): View
    {
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        $invitedFriendsQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('friends')
            ->where('friend_id = ?')
            ->setParameter(0, $activeId)
            ->executeQuery()
            ->fetchAllAssociative();

        $invitedFriends = [];
        foreach ($invitedFriendsQuery as $invitedFriend) {
            $invitedFriends [] = [(int)$invitedFriend['user_id'], (int) $invitedFriend['friend_id']];
        }

        $acceptedFriendsQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('friends')
            ->where('user_id = ?')
            ->setParameter(0, $activeId)
            ->executeQuery()
            ->fetchAllAssociative();

        $acceptedFriends = [];
        foreach ($acceptedFriendsQuery as $acceptedFriend) {
            $acceptedFriends [] = [(int)$acceptedFriend['user_id'], (int)$acceptedFriend['friend_id']];
        }

        $friendsIds= [];
        foreach ($invitedFriends as $invitedFriend){
            foreach ($acceptedFriends as $acceptedFriend){
                if($invitedFriend[0]==$acceptedFriend[1] && $invitedFriend[1]==$acceptedFriend[0]){
                    $friendsIds [] = $invitedFriend[0];
                }
            }
        }

        $myPendingInvitationFromIds= [];
        foreach ($acceptedFriends as $acceptedFriend){
            if(!in_array(($acceptedFriend[1]), $friendsIds)){
                $myPendingInvitationFromIds[]=$acceptedFriend[1];
            }
        }
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

        $invites = [];
        foreach ($usersQuery as $userData) {
            foreach ($userProfilesQuery as $userProfileData) {
                if($userData['id']==$userProfileData['user_id'] && in_array($userData['id'], $myPendingInvitationFromIds)) {
                    $invites [] = new User(
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

        //---------------------------------------

//        $iHaveInvitedIds= [];
//        foreach ($invitedFriends as $invitedFriend){
//            if(!in_array(($invitedFriend[0]), $friendsIds)){
//                $iHaveInvitedIds[]=$invitedFriend[0];
//            }
//        }
//        echo "<pre>";
//        var_dump($iHaveInvitedIds);
//        var_dump($friendsIds);
//        var_dump($myPendingInvitationFromIds);die;
        return new View('Friends/invites', [
            'invites' => $invites,
            'active' => $active,
            'activeId' => $activeId
        ]);
    }

    public function show(array $vars): View
    {
        $active = $_SESSION["name"];
        $activeId = $_SESSION["id"];
        $invitedFriendsQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('friends')
            ->where('friend_id = ?')
            ->setParameter(0, $activeId)
            ->executeQuery()
            ->fetchAllAssociative();

        $invitedFriends = [];
        foreach ($invitedFriendsQuery as $invitedFriend) {
            $invitedFriends [] = [(int)$invitedFriend['user_id'], (int) $invitedFriend['friend_id']];
        }

        $acceptedFriendsQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('friends')
            ->where('user_id = ?')
            ->setParameter(0, $activeId)
            ->executeQuery()
            ->fetchAllAssociative();

        $acceptedFriends = [];
        foreach ($acceptedFriendsQuery as $acceptedFriend) {
            $acceptedFriends [] = [(int)$acceptedFriend['user_id'], (int)$acceptedFriend['friend_id']];
        }

        $friendsIds= [];
        foreach ($invitedFriends as $invitedFriend){
            foreach ($acceptedFriends as $acceptedFriend){
                if($invitedFriend[0]==$acceptedFriend[1] && $invitedFriend[1]==$acceptedFriend[0]){
                    $friendsIds [] = $invitedFriend[0];
                }
            }
        }

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

        $friends = [];
        foreach ($usersQuery as $userData) {
            foreach ($userProfilesQuery as $userProfileData) {
                if($userData['id']==$userProfileData['user_id'] && in_array($userData['id'], $friendsIds)) {
                    $friends [] = new User(
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

        return new View('Friends/friends', [
            'friends' => $friends,
            'active' => $active,
            'activeId' => $activeId
        ]);
    }



}