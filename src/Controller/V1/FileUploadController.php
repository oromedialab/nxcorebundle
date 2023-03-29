<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;
use OroMediaLab\NxCoreBundle\Entity\User;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use OroMediaLab\NxCoreBundle\Utils\Common;

class FileUploadController extends AbstractController
{
    public function upload(Request $request, ManagerRegistry $doctrine): ApiResponse
    {
        $entityManager = $doctrine->getManager();
        $postData = $request->request->all();
        $isDev = 'dev' === $this->container->getParameter('kernel.environment');
        $uploadedFiles = $request->files;
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            return new ApiResponse(ApiResponseCode::FILE_NOT_UPLOADED);
        }
        $accessKey = 'DO008CNG6TMALZVZB92C';
        $secretKey = '+HeYBiHnggbo4gb2uWNKVc+s7rPx6S6qJTW3jKTVfsA';
        $endpoint = 'https://nyc3.digitaloceanspaces.com';
        $cdnEndpoint = 'https://cosko.nyc3.cdn.digitaloceanspaces.com';
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'nyc3',
            'endpoint' => $endpoint,
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ]
        ]);

        $year = date('Y', strtotime('now'));
        $month = date('m', strtotime('now'));
        $fileNameWithoutExtension = Common::generateRandomString(rand(36, 64));
        $fileExtension = $file->guessExtension();
        $fileName = $fileNameWithoutExtension.'.'.$fileExtension;
        $fileNameWithBaseDir = $year.'/'.$month.'/'.$fileName;
        if (true === $isDev) {
            $fileNameWithBaseDir = 'dev/'.$fileNameWithBaseDir;
        }
        try {
            $s3->putObject([
                'Bucket' => 'cosko',
                'Key' => $fileNameWithBaseDir,
                'Body' => fopen($file->getPathname(), 'rb'),
                'ACL' => 'public-read'
            ]);
        } catch (AwsException $e) {
            throw new \Exception('Error uploading file: ' . $e->getMessage());
        }
        return new ApiResponse(ApiResponseCode::FILE_UPLOADED, [
            'base_url' => $cdnEndpoint,
            'mime_type' => mime_content_type($cdnEndpoint.'/'.$fileNameWithBaseDir),
            'files' => array(
                'file' => '/'.$fileNameWithBaseDir
            )
        ]);
    }
}
