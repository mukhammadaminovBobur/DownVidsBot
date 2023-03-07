<?php
namespace App\Http\Traits;


use function PHPUnit\Framework\isNull;

trait DataTrait{
    use BotTrait;

    public function getUser($update)
    {
        if (isset($update->callback_query)) {
            $user_id = $update->callback_query->from->id;
            $name = $update->callback_query->from->first_name;
        }
        if (isset($update->message)) {
            $user_id = $update->message->from->id;
            $name = $update->message->from->first_name;
        }
        $checkUser = $this->usersModel::where('user_id', $user_id)->first();
        if (!$checkUser){
            $this->usersModel::create([
                'user_id' => $user_id,
            ]);
            $checkUser = $this->usersModel::where('user_id', $user_id)->first();
            $txt = "*Yangi foydalanuvchi: \n\n📝 Ism: {$name}\n🆔 ID: *`{$user_id}`*\n\n@{$this->botUser}*";
            $this->sendMessage($this->botDev, $txt, ['parse_mode' => 'markdown']);
        }
        if ($checkUser->deleted_at != null){
            $this->updateUser($checkUser->user_id, ['deleted_at' => null]);
        }
        return $checkUser;
    }
    public function updateUser($user_id, $data){
        return $this->usersModel::where('user_id', $user_id)->first()->update($data);
    }

    public function getGroup($update)
    {
        if (isset($update->callback_query)) {
            $chat_id = $update->callback_query->message->chat->id;
            $chat_title = $update->callback_query->message->chat->title;
        }
        if (isset($update->message)) {
            $chat_id = $update->message->chat->id;
            $chat_title = $update->message->chat->title;
        }

        $checkGroup = $this->groupsModel::where('group_id', $chat_id)->first();
        if (!$checkGroup){
            $this->groupsModel::create([
                'group_id' => $chat_id,
            ]);
            $checkGroup = $this->groupsModel::where('group_id', $chat_id)->first();
            $txt = "*Yangi guruh: \n\n📝 Nomi: {$chat_title}\n🆔 ID: *`{$chat_id}`*\n\n@{$this->botUser}*";
            $this->sendMessage($this->botDev, $txt, ['parse_mode' => 'markdown']);
        }
        if ($checkGroup->deleted_at != null){
            $this->updateGroup($checkGroup->group_id, ['deleted_at' => null]);
        }
        return $checkGroup;
    }

    public function updateGroup($group_id, $data)
    {
        return $this->groupsModel::where('group_id', $group_id)->first()->update($data);
    }

}
