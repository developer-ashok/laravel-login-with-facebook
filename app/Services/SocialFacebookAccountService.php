<?php
namespace App\Services;
use App\SocialFacebookAccount;
use App\User;
use Laravel\Socialite\Contracts\User as ProviderUser;


use Illuminate\Support\Facades\Mail;
use App\Mail\welcomeMail;

class SocialFacebookAccountService
{
    public function createOrGetUser(ProviderUser $providerUser)
    {
        $account = SocialFacebookAccount::whereProvider('facebook')
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $account = new SocialFacebookAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'facebook'
            ]);
            $user = User::whereEmail($providerUser->getEmail())->first();
            if (!$user) {
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'password' => md5(uniqid()), // we use md5 where user will not able to use this as normal login :)
                    'role'=>'customer',
                ]);

                if($user){
                    $data = [];
                    $data['email'] = $providerUser->getEmail();
                    $data['name'] = $providerUser->getName();                     
                }
            }
            $account->user()->associate($user);
            $account->save();
            return $user;
        }
    }
}
