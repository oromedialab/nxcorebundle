Step1: Install Bundle
    `composer require oromedialab/nxcorebundle`

Step2: Import Routes
    Go to project-dir/config/routes.yaml and add the following
    nxcore_routes:
        resource: '@NxCoreBundle/Config/routing.yaml'

Step3: Enable Bundles
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Knp\DoctrineBehaviors\DoctrineBehaviorsBundle::class => ['all' => true],
    Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true],
    OroMediaLab\NxCoreBundle\NxCoreBundle::class => ['all' => true]

Step4: Copy all packages yaml located at @NxCoreBundle/config/packages to project-dir/config/packages

Step5: Update .env with following content
Add this in the .env file
###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=1520850c42401bec335956ef6d7d5d26
###< lexik/jwt-authentication-bundle ###

Step6: Follow Installation Instructions From (skip composer require part)
https://github.com/lexik/LexikJWTAuthenticationBundle
