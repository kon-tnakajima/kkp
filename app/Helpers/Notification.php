<?php
declare(strict_types=1);
/**
 * チャットツールへメッセージを送信
 * @param string $channel  チャンネル名
 * @param string $userName 名前
 * @param string $body     内容
 */
function sendChatMessage(string $channel, string $userName, string $body)
{
    $prefixChannelName = '';
    $dbname = \Config::get('database.connections.pgsql.database');
    // データベース名より、テスト、開発、ステージング、本番を判断
    if (strpos($dbname, 'test') !== false) {
        // テスト環境
        $prefixChannelName = 'test_';
    } elseif (strpos($dbname, 'dev') !== false) {
        // 開発環境
        $prefixChannelName = 'dev_';
    } elseif (strpos($dbname, 'staging') !== false) {
        // ステージング環境
        $prefixChannelName = 'staging_';
    } else {
        // 本番環境
        $prefixChannelName = 'production_';
    }
    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', 'https://hooks.slack.com/services/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',
        [
            'json' => [
                "channel"     => $prefixChannelName.$channel,
                "username"    => $userName,
                "text"        => substr($body, 0, 128)
            ]
        ]
    );
}

