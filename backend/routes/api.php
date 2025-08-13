<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ImageUploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::group([
        'middleware' => ['auth:api', 'auth.online']
    ], function() {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::post('messages', [ChatController::class, 'sendMessage']);
    Route::get('messages/{channel_id}', [ChatController::class, 'getMessages']);
    Route::get('getchannels', [ChatController::class, 'getSubscribedChannels']);
    Route::get('getusers/{channel_id}', [ChatController::class, 'getChannelsUsers']);
    Route::get('getallchannels', [ChatController::class, 'getAllChannels']);
    Route::post('makerequest', [ChatController::class, 'createInvite']);
    Route::get('acceptinvite/{invite_id}', [ChatController::class, 'acceptRequest']);
    Route::get('online/{user_id}', [ChatController::class, 'isOnline']);
    Route::get('offline/{user_id}', [ChatController::class, 'isOffline']);
    Route::get('getfriendslist', [ChatController::class, 'getFriendsList']);
    Route::get('notifications', [ChatController::class, 'getNotifications']);
    Route::get('allnotifications', [ChatController::class, 'getAllNotifications']);
    Route::get('markasread/{id}', [ChatController::class, 'markNotificationAsRead']);
    Route::post('directmessage', [ChatController::class, 'directMessage']);
    Route::get('allusers', [AuthController::class, 'allUsersList']);
    Route::post('createchannel', [ChatController::class, 'createChannel']);
    Route::post('joinchannel', [ChatController::class, 'joinChannel']);
    Route::post('invitetochannel', [ChatController::class, 'inviteToChannel']);
    Route::post('upload/profile', [ImageUploadController::class, 'updateProfilePicture']);
    Route::post('editdesc', [ChatController::class, 'updateUserDesc']);
});