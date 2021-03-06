<?php

namespace Zenderator\Generators;

use Zend\Stdlib\ConsoleHelper;
use Zenderator\Components\Column;
use Zenderator\Interfaces\IZenderatorGenerator;
use Zenderator\Zenderator;

class PhpSdkGenerator extends BaseGenerator
{
    protected $baseTemplatePath = __DIR__ . "/../../generator/templates";

    public function generate()
    {
        $this->generateCoreFiles();
        //$this->generateTests($packName, $routeRenderData);
        $this->generateBaseFiles();

        return $this;
    }

    private function getSDKConfig()
    {
        return $this->zenderator->getConfig()["sdk"] ?? [];
    }

    private function getDesiredSDKTemplates()
    {
        return $this->getSDKConfig()["templates"] ?? [];
    }

    private function generateBaseFiles()
    {
        $renderData = [
            "accessLayers"           => array_keys($this->getDataProvider()->getAccessLayerData()),
            "defaultUrl"             => strtolower("http://" . $this->getDataProvider()->getAppName() . ".segurasystems.test"),
            "namespace"              => $this->getDataProvider()->getNameSpace(),
            "appName"                => $this->getDataProvider()->getAppName(),
            "classNamespace"         => $this->getDataProvider()->getBaseClassNameSpace(),
            "classNamespaceJSONSAFE" => $this->getDataProvider()->getBaseClassNameSpace(true),
            "releaseTime"            => date("Y-m-d H:i:s"),
            "config"                 => $this->getSDKConfig(),
        ];
        echo "\n";
        echo str_pad("Generating Dependency Injector:", 50);
        $this->renderToFile(true, "/src/DependencyInjector.php", "SDK/dependencyinjector.php.twig", $renderData);
        echo " [" . ConsoleHelper::COLOR_GREEN . "DONE" . ConsoleHelper::COLOR_RESET . "]\n";

        echo str_pad("Generating Client Container:", 50);
        $this->renderToFile(true, "/src/Client.php", "SDK/client.php.twig", $renderData);
        echo " [" . ConsoleHelper::COLOR_GREEN . "DONE" . ConsoleHelper::COLOR_RESET . "]\n";

        echo str_pad("Generating Composer.json:", 50);
        $this->renderToFile(true, "/composer.json", "SDK/composer.json.twig", $renderData);
        echo " [" . ConsoleHelper::COLOR_GREEN . "DONE" . ConsoleHelper::COLOR_RESET . "]\n";

        echo str_pad("Generating Test Bootstrap:", 50);
        $this->renderToFile(true, "/bootstrap.php", "SDK/bootstrap.php.twig", $renderData);
        echo " [" . ConsoleHelper::COLOR_GREEN . "DONE" . ConsoleHelper::COLOR_RESET . "]\n";

        echo str_pad("Generating phpunit.xml, documentation, etc:", 50);
        $this->renderToFile(true, "/phpunit.xml.dist", "SDK/phpunit.xml.twig", $renderData);
        $this->renderToFile(true, "/Readme.md", "SDK/readme.md.twig", $renderData);
        $this->renderToFile(true, "/.gitignore", "SDK/gitignore.twig", $renderData);
        $this->renderToFile(true, "/Dockerfile.tests", "SDK/Dockerfile.twig", $renderData);
        $this->renderToFile(true, "/test-compose.yml", "SDK/docker-compose.yml.twig", $renderData);
        echo " [" . ConsoleHelper::COLOR_GREEN . "DONE" . ConsoleHelper::COLOR_RESET . "]\n";
    }

    private function generateCoreFiles()
    {
        $modelData = $this->getDataProvider()->getModelData();
        $accessLayerData = $this->getDataProvider()->getAccessLayerData();
        $this->generateCoreFile($modelData,"Model");
        $this->generateCoreFile($modelData, "Validator");
        $this->generateCoreFile($modelData, "Cleaner");
        $this->generateCoreFile($accessLayerData, "AccessLayer", "SDK");
    }

    private function generateCoreFile($modelData, string $fileType, $templateFolder = "Classes")
    {
        if (!in_array("{$fileType}s", $this->getDesiredSDKTemplates())) {
            return;
        }

        print "\nGenerating {$fileType}s ...\n";
        foreach ($modelData as $className => $class) {
            print str_pad("   > {$className}", 40);
            print " Base";
            $this->renderToFile(true, "/src/{$fileType}s/Base/Base{$className}{$fileType}.php", "{$templateFolder}/{$fileType}s/Base/Base{classname}{$fileType}.php.twig", ["sdk"=>true,"class" => $class]);
            print " Main";
            $this->renderToFile(false, "/src/{$fileType}s/{$className}{$fileType}.php", "{$templateFolder}/{$fileType}s/{classname}{$fileType}.php.twig", ["sdk"=>true,"class" => $class]);
            echo " [" . ConsoleHelper::COLOR_GREEN . "DONE" . ConsoleHelper::COLOR_RESET . "]\n";
        }
    }

    /**
     * @param $packName
     * @param $routeRenderData
     */
    private function generateTests($packName, $routeRenderData): void
    {
// Tests
        $this->renderToFile(true, "/tests/AccessLayer/{$packName}Test.php", "SDK/Tests/AccessLayer/client.php.twig", $routeRenderData);

        $this->mkdir("/tests/fixtures/touch");
    }
}