<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait WordsTrait{

    public function words($lang, $key, $name = '')
    {
        $data = [
            'uzbek' => [
                "en" => "🇺🇿O'zbek tili",
                "ru" => "🇺🇿O'zbek tili",
                "uz" => "🇺🇿O'zbek tili",
            ],
            'russian' => [
                "en" => "🇷🇺Русский",
                "ru" => "🇷🇺Русский",
                "uz" => "🇷🇺Русский",
            ],
            'english' => [
                "en" => "🇺🇸English",
                "ru" => "🇺🇸English",
                "uz" => "🇺🇸English",
            ],
            'sizeLimit' => [
                "en" => "The size of video you trying to download is more than 20 MB. You can still download the video by clicking the link below",
                "ru" => "Размер видео, которое вы пытаетесь загрузить, превышает 20 МБ. Вы все еще можете скачать видео, нажав на ссылку ниже",
                "uz" => "Siz yuklab olmoqchi bo'lgan videoning hajmi 20 MB dan ortiq. Siz hali ham quyidagi havolani bosish orqali videoni yuklab olishingiz mumkin",
            ],
            'shareFriends' => [
                "en" => "↗Share friends",
                "ru" => "↗Поделиться друзьями",
                "uz" => "↗Do'stlarga ulashing",
            ],
            'welcome' => [
                "en" => "Hello {$name} 👋\nI'm a bot for downloading tiktok videos inside telegram.\n\nI can download video without watermark. Simply send me a tiktok url.",
                "uz" => "Assalomu alaykum {$name} 👋\nMen telegram ichidagi tiktok videolarni yuklab oladigan botman.\n\nVideolarni hech qanday belgisisiz yuklab olishim mumkin. Menga tiktok URL manzilini yuboring.",
                "ru" => "Здравствуйте, {$name} 👋\nЯ бот для скачивания видео с тикток внутри телеграммы.\n\nЯ могу скачивать видео без водяных знаков. Просто пришлите мне ссылку на тикток."
            ],
            'groupWelcome' => [
                "en" => "Hello 👋\nI'm a bot for downloading tiktok videos inside telegram.\n\nI can download video without watermark. Simply send me a tiktok url. Send `/settings` for settings",
                "ru" => "Здравствуйте 👋\nЯ бот для скачивания видео с тикток внутри телеграммы.\n\nЯ могу скачивать видео без водяных знаков. Просто пришлите мне ссылку на тикток. Отправить `/settings` для настроек",
                "uz" => "Assalomu alaykum 👋\nMen telegram ichidagi tiktok videolarni yuklab oladigan botman.\n\nVideolarni hech qanday belgisisiz yuklab olishim mumkin. Menga tiktok URL manzilini yuboring. Sozlamalar uchun `/settings` yuboring",
            ],
            'back' => [
                'en' => "Back🔙",
                'ru' => "Назад🔙",
                'uz' => "Orqaga🔙",
            ],
            'cancel' => [
                'en' => "Cancel🚫",
                'ru' => "Отмена🚫",
                'uz' => "Bekor qilish🚫",
            ],
            'mainMenu' => [
                'en' => "Main menu🔝",
                'ru' => "Главное меню🔝",
                'uz' => "Asosiy menyu🔝",
            ],
            'languageSet' => [
                'en' => "Language set to english✅",
                'ru' => "Язык установлен русский✅",
                'uz' => "Til o'zbek tiliga o'rnatildi✅",
            ],
            'statistic' => [
                'en' => "Statistics📊",
                'ru' => "Статистика📊",
                'uz' => "Statistika📊",
            ],
            'adminPanel' => [
                'en' => "Admin Panel🛠",
                'ru' => "Панель администратора🛠",
                'uz' => "Admin paneli🛠",
            ],
            'notFound' => [
                "en" => "Not found🤷‍♂️",
                "ru" => "Не найдено🤷‍♂️",
                "uz" => "Topilmadi🤷‍♂️",
            ],
            'wait' => [
                "en"  => "Please, wait...",
                "ru" => "Пожалуйста, подождите...",
                "uz" => "Iltimos kuting...",
            ],
            'checkLink' => [
                "en" => "Failed to get data from tiktok. Maybe the link is removed by it's owner, or that ID does not exist.",
                "ru" => "Не удалось получить данные из тикток. Возможно, ссылка удалена ее владельцем или такого идентификатора не существует.",
                "uz" => "Tiktokdan maʼlumotlarni olib boʻlmadi. Balki havola uning egasi tomonidan olib tashlangan yoki bu identifikator mavjud emas.",
            ],
            'downloadedWith' => [
                "en" => "✅Downloaded with @{$this->botUser}",
                "ru" => "✅Скачано с помощью @{$this->botUser}",
                "uz" => "@{$this->botUser} bilan yuklab olingan✅"
            ],
            'settings' => [
                "en" => "Settings⚙",
                "ru" => "Настройки⚙",
                "uz" => "Sozlamalar⚙",
            ],
            "chooseLang" => [
                "en" => "Please choose language",
                "ru" => "Пожалуйста, выберите язык",
                "uz" => "Iltimos, tilni tanlang",
            ],
            "changeLang" => [
                "en" => "Change language🔄",
                "ru" => "Сменить язык🔄",
                "uz" => "Tilni o'zgartirish🔄",
            ],
            "musicOn" => [
                "en" => "Music✅",
                "ru" => "Музыка✅",
                "uz" => "Musiqa✅",
            ],
            "musicOff" => [
                "en" => "Music☑",
                "ru" => "Музыка☑",
                "uz" => "Musiqa☑",
            ],
            "musicOnText" => [
                "en" => "When you download video, music also will be sent✅",
                "ru" => "Когда вы загружаете видео, музыка также будет отправлена✅",
                "uz" => "Videoni yuklab olganingizda musiqa ham yuboriladi✅",
            ],
            "musicOffText" => [
                "en" => "When you download video, music will not be sent☑",
                "ru" => "При скачивании видео музыка отправляться не будет☑",
                'uz' => "Video yuklaganingizda musiqa yuborilmaydi☑",
            ],
            "botSubs" => [
                "en" => "👤 Bot subscribers",
                "ru" => "👤 Подписчики бота",
                "uz" => "👤 Bot obunachilari",
            ],
            "last24h" => [
                "en" => "🔜 Last 24 hour",
                "ru" => "🔜 Последние 24 часа",
                "uz" => "🔜 Oxirgi 24 soat",
            ],
            "lastMonth" => [
                "en" => "🔝 Last month",
                "ru" => "🔝 Последний месяц",
                "uz" => "🔝 O'tgan oy",
            ],
            "tiktokPosts" => [
                "en" => "📹Tiktok posts",
                "ru" => "📹Тикток посты",
                "uz" => "📹Tiktok postlari",
            ],
            "instagramPosts" => [
                "en" => "📹Instagram videos",
                "ru" => "📹Инстаграм посты",
                "uz" => "📹Instagram postlari",
            ],
            "date" => [
                "en" => "📅 Date",
                "ru" => "📅 Дата",
                "uz" => "📅 Sana",
            ],
            "refresh" => [
                "en" => "Refresh🔁",
                "ru" => "Обновить🔁",
                "uz" => "Yangilash🔁",
            ],
            "totalGroups" => [
                "en" => "👥Total groups",
                "ru" => "👥Всего групп",
                "uz" => "👥Barcha guruhlar"
            ],


        ];
        return $data[$key][$lang];
    }

}
