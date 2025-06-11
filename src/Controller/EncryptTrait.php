<?php

namespace WechatOpenPlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Tourze\WechatHelper\Encryptor;
use Tourze\WechatHelper\XML;
use WechatOpenPlatformBundle\Entity\Account;

trait EncryptTrait
{
    private function decryptMessage(array $message, Request $request, Account $account): string
    {
        $encryptor = new Encryptor(
            $account->getAppId(),
            $account->getToken(),
            $account->getAesKey(),
        );

        return $encryptor->decrypt(
            $message['Encrypt'],
            $request->query->get('msg_signature'),
            $request->query->get('nonce'),
            $request->query->get('timestamp')
        );
    }

    private function parseMessage(string $content): ?array
    {
        try {
            if (0 === mb_stripos($content, '<')) {
                $content = XML::parse($content);
            } else {
                // Handle JSON format.
                $dataSet = json_decode($content, true);
                if ($dataSet && (JSON_ERROR_NONE === json_last_error())) {
                    $content = $dataSet;
                }
            }

            return (array) $content;
        } catch  (\Throwable $e) {
            throw new BadRequestException(sprintf('Invalid message content:(%s) %s', $e->getCode(), $e->getMessage()), $e->getCode());
        }
    }
}
