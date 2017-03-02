<?php

use Pulq\Util\Agavi\Action\BaseAction;
use Pulq\Consumer\Services\SignatureService;

class Util_LoadFixturesAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $fixture_set = $rd->getParameter('fixture');

        $fixtures_dir = \AgaviConfig::get('core.fixtures_dir');

        $glob = $fixtures_dir.DIRECTORY_SEPARATOR.$fixture_set.DIRECTORY_SEPARATOR.'*';

        $files = glob($glob);

        foreach($files as $file) {
            $pathinfo = pathinfo($file);

            $id = $pathinfo['filename'];
            $document = $this->getDocument($file);
            $document['_id'] = $id;

            $this->pushDocument($id, $document);
        }

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }

    protected function getDocument($filepath)
    {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);

        if ($ext === "json") {
            $document = json_decode(file_get_contents($filepath), true);
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
            $mime = finfo_file($finfo, $filepath);
            finfo_close($finfo);

            $document = array(
                "filename" => basename($filepath),
                "size" => filesize($filepath),
                "type" => "asset",
                "mime" => $mime,
                "modified" => date('c', filemtime($filepath)),
                "data" => base64_encode(file_get_contents($filepath)),
                "copyright" => "Testing Content ".date('Y'),
                "copyright_url" => "http://example.org",
                "caption" => "Lorem ipsum dolor sit amet",
                "live" => true
            );
        }

        return $document;
    }

    protected function pushDocument($id, $document)
    {
        $url = $this->getPushUrl($id);
        $signature = $this->getSignature($id);

        $curl = curl_init($url);
        $headers = array(
            "Content-Type: application/json",
            "Signature: $signature"
        );
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($document));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);

        curl_close($curl);
    }

    protected function getPushUrl($id)
    {
        $routing = \AgaviContext::getInstance('web')->getRouting();

        $url = $routing->gen(
            'consumer.push',
            array(
                'id' => $id
            ),
            array(
                'prefix' => '',
                'relative' => true
            )
        );

        $env_path = \AgaviConfig::get('core.app_dir').'/../etc/local/local.config.php';

        $env = require($env_path);

        $base_href = $env['pulq_environment']['base_href'];

        return $base_href.$url;
    }

    protected function getSignature($id)
    {
        $signature_service = new SignatureService();

        return $signature_service->sign($id);
    }
}
