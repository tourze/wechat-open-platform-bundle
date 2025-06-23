<?php

namespace WechatOpenPlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Request\CreatePreAuthCodeRequest;
use WechatOpenPlatformBundle\Service\ApiService;

/**
 * 授权入口页
 */
class AuthIndexController extends AbstractController
{
    public function __construct(
        private readonly ApiService $apiService,
    ) {
    }

    #[Route('/wechat-open-platform/auth/{appId}')]
    public function __invoke(
        Account $account,
        Request $request,
    ): Response {
        $options = [
            'auth_type' => 3, // 1 表示手机端仅展示公众号；2 表示仅展示小程序，3 表示公众号和小程序都展示。如果为未指定，则默认小程序和公众号都展示。
        ];
        if ($request->query->has('biz_appid')) {
            // 指定AppID
            $options['biz_appid'] = $request->query->get('biz_appid');
        }

        $callbackUrl = $this->generateUrl('wechat-open-platform-auth-callback', [
            'appId' => $account->getAppId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $codeRequest = new CreatePreAuthCodeRequest();
        $codeRequest->setAccount($account);
        $codeResponse = $this->apiService->request($codeRequest);
        $options['pre_auth_code'] = $codeResponse['pre_auth_code'] ?? null;

        $queries = \array_merge(
            $options,
            [
                'component_appid' => $account->getAppId(),
                'redirect_uri' => $callbackUrl,
            ]
        );

        return $this->render('@WechatOpenPlatform/redirect.html.twig', [
            'url' => 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?' . http_build_query($queries),
        ]);
    }
}