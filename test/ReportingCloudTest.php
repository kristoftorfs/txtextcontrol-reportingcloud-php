<?php

namespace TxTextControlTest\ReportingCloud;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;

use TxTextControl\ReportingCloud\Exception\InvalidArgumentException;
use TxTextControl\ReportingCloud\Exception\RuntimeException;
use TxTextControl\ReportingCloud\ReportingCloud;
use TxTextControl\ReportingCloud\CliHelper as Helper;
use TxTextControl\ReportingCloud\Validator\ReturnFormat as ReturnFormatValidator;

class ReportingCloudTest extends PHPUnit_Framework_TestCase
{
    protected $reportingCloud;

    public function setUp()
    {
        $this->reportingCloud = new ReportingCloud();

        $this->assertNotEmpty(Helper::username());
        $this->assertNotEmpty(Helper::password());

        $this->reportingCloud->setUsername(Helper::username());
        $this->reportingCloud->setPassword(Helper::password());
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testGetTemplateInfo()
    {
        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        $response = $this->reportingCloud->getTemplateInfo($tempTemplateName);

        $this->assertArrayHasKey('template_name'      , $response);

        $this->assertArrayHasKey('merge_blocks'       , $response);

        $this->assertArrayHasKey(0                    , $response['merge_blocks']);

        $this->assertArrayHasKey('name'               , $response['merge_blocks'][0]);
        $this->assertArrayHasKey('merge_fields'       , $response['merge_blocks'][0]);

        $this->assertArrayHasKey(0                    , $response['merge_blocks'][0]['merge_fields']);

        $this->assertArrayHasKey('date_time_format'   , $response['merge_blocks'][0]['merge_fields'][0]);
        $this->assertArrayHasKey('numeric_format'     , $response['merge_blocks'][0]['merge_fields'][0]);
        $this->assertArrayHasKey('preserve_formatting', $response['merge_blocks'][0]['merge_fields'][0]);
        $this->assertArrayHasKey('text'               , $response['merge_blocks'][0]['merge_fields'][0]);
        $this->assertArrayHasKey('text_after'         , $response['merge_blocks'][0]['merge_fields'][0]);
        $this->assertArrayHasKey('text_before'        , $response['merge_blocks'][0]['merge_fields'][0]);

        $this->assertArrayHasKey('merge_fields'       , $response);

        $this->assertArrayHasKey(0                    , $response['merge_fields']);

        $this->assertArrayHasKey('date_time_format'   , $response['merge_fields'][0]);
        $this->assertArrayHasKey('numeric_format'     , $response['merge_fields'][0]);
        $this->assertArrayHasKey('preserve_formatting', $response['merge_fields'][0]);
        $this->assertArrayHasKey('text'               , $response['merge_fields'][0]);
        $this->assertArrayHasKey('text_after'         , $response['merge_fields'][0]);
        $this->assertArrayHasKey('text_before'        , $response['merge_fields'][0]);

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testGetTemplateThumbnails()
    {
        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        $response = $this->reportingCloud->getTemplateThumbnails($tempTemplateName, 100, 1, 1, 'PNG');

        $this->assertArrayHasKey(0, $response);

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTemplateThumbnailsInvalidTemplateName()
    {
        $this->reportingCloud->getTemplateThumbnails('sample_invoice.xx', 100, 1, 1, 'PNG');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTemplateThumbnailsInvalidZoomFactor()
    {
        $this->reportingCloud->getTemplateThumbnails('sample_invoice.tx', -1, 1, 1, 'PNG');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTemplateThumbnailsInvalidFromPage()
    {
        $this->reportingCloud->getTemplateThumbnails('sample_invoice.tx', 100, -1, 1, 'PNG');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTemplateThumbnailsInvalidToPage()
    {
        $this->reportingCloud->getTemplateThumbnails('sample_invoice.tx', 100, 1, -1, 'PNG');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTemplateThumbnailsInvalidImageFormat()
    {
        $this->reportingCloud->getTemplateThumbnails('sample_invoice.tx', 100, 1, 1, 'XXX');
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testGetTemplateCount()
    {
        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        $response = $this->reportingCloud->getTemplateCount();

        $this->assertTrue(is_integer($response));

        $this->assertGreaterThan(0, $response);

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTemplateExistsInvalidTemplateName()
    {
        $this->reportingCloud->templateExists('sample_invoice.xx');
    }

    public function testTemplateExists()
    {
        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        $response = $this->reportingCloud->templateExists($tempTemplateName);

        $this->assertTrue($response);

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);

        $response = $this->reportingCloud->templateExists($tempTemplateName);

        $this->assertFalse($response);

        unlink($tempTemplateFilename);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTemplatePageCountInvalidTemplateName()
    {
        $this->reportingCloud->getTemplatePageCount('sample_invoice.xx');
    }

    public function testGetTemplatePageCount()
    {
        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        $response = $this->reportingCloud->getTemplatePageCount($tempTemplateName);

        $this->assertSame(1, $response);

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testGetTemplateList()
    {
        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        $response = $this->reportingCloud->getTemplateList();

        $this->assertArrayHasKey(0, $response);

        $this->assertArrayHasKey('template_name', $response[0]);
        $this->assertArrayHasKey('modified'     , $response[0]);
        $this->assertArrayHasKey('size'         , $response[0]);

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testGetAccountSettings()
    {
        $response = $this->reportingCloud->getAccountSettings();

        $this->assertArrayHasKey('serial_number'     , $response);
        $this->assertArrayHasKey('created_documents' , $response);
        $this->assertArrayHasKey('uploaded_templates', $response);
        $this->assertArrayHasKey('max_documents'     , $response);
        $this->assertArrayHasKey('max_templates'     , $response);
        $this->assertArrayHasKey('valid_until'       , $response);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertDocumentInvalidDocumentFilenameUnsupportedExtension()
    {
        $this->reportingCloud->convertDocument('/invalid/path/document.xxx', 'PDF');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertDocumentInvalidDocumentFilenameNoExtension()
    {
        $this->reportingCloud->convertDocument('/invalid/path/document', 'PDF');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertDocumentInvalidDocumentFilenameNoFile()
    {
        $this->reportingCloud->convertDocument('/invalid/path/document/', 'PDF');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertDocumentInvalidDocumentFilename()
    {
        $this->reportingCloud->convertDocument('/invalid/path/document.doc', 'PDF');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertDocumentInvalidReturnFormat()
    {
        $documentFilename = $this->getTestTemplateFilename();

        $this->reportingCloud->convertDocument($documentFilename, 'XXX');
    }

    public function testConvertDocument()
    {
        $documentFilename = $this->getTestDocumentFilename();

        $this->assertFileExists($documentFilename);

        $response = $this->reportingCloud->convertDocument($documentFilename, 'PDF');
        $responseLength = mb_strlen($response);

        $this->assertNotNull($response);
        $this->assertNotFalse($response);
        $this->assertGreaterThanOrEqual(1024, $responseLength);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidReturnFormat()
    {
        $mergeData = $this->getTestTemplateMergeData();

        $this->reportingCloud->mergeDocument($mergeData, 'X');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidTemplateName()
    {
        $mergeData = $this->getTestTemplateMergeData();

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', '../invalid_template.tx');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidTemplateFilenameUnsupportedExtension()
    {
        $mergeData = $this->getTestTemplateMergeData();

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, '/invalid/path/template.xxx');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidTemplateFilenameNoExtension()
    {
        $mergeData = $this->getTestTemplateMergeData();

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, '/invalid/path/template');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidTemplateFilenameNoFile()
    {
        $mergeData = $this->getTestTemplateMergeData();

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, '/invalid/path/template/');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidTemplateFilename()
    {
        $mergeData = $this->getTestTemplateMergeData();

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, '/invalid/path/template.doc');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidAppend()
    {
        $mergeData        = $this->getTestTemplateMergeData();
        $templateFilename = $this->getTestTemplateFilename();

        $this->assertFileExists($templateFilename);

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, $templateFilename, 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidMergeSettingsIntegerInsteadOfArray()
    {
        $mergeData        = $this->getTestTemplateMergeData();
        $templateFilename = $this->getTestTemplateFilename();

        $this->assertFileExists($templateFilename);

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, $templateFilename, true, 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidMergeSettingsStringInsteadOfBoolean()
    {
        $mergeData        = $this->getTestTemplateMergeData();
        $mergeSettings    = $this->getTestMergeSettings();

        $templateFilename = $this->getTestTemplateFilename();

        $this->assertFileExists($templateFilename);

        $mergeSettings['remove_empty_blocks'] = 'invalid';  // value must be boolean
        $mergeSettings['remove_empty_fields'] = 'invalid';
        $mergeSettings['remove_empty_images'] = 'invalid';

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, $templateFilename, false, $mergeSettings);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeInvalidMergeSettingsTimestampValues()
    {
        $mergeData        = $this->getTestTemplateMergeData();
        $mergeSettings    = $this->getTestMergeSettings();

        $templateFilename = $this->getTestTemplateFilename();

        $this->assertFileExists($templateFilename);

        $mergeSettings['creation_date']          = -1;  // value must be timestamp
        $mergeSettings['last_modification_date'] = 'invalid';

        $this->reportingCloud->mergeDocument($mergeData, 'PDF', null, $templateFilename, false, $mergeSettings);
    }

    public function testMergeWithTemplateName()
    {
        $returnFormats        = $this->getTestReturnFormats();

        $mergeData            = $this->getTestTemplateMergeData();
        $mergeSettings        = $this->getTestMergeSettings();

        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);

        foreach ($returnFormats as $returnFormat) {

            $response = $this->reportingCloud->mergeDocument($mergeData, $returnFormat, $tempTemplateName, null, false, $mergeSettings);

            $this->assertNotNull($response);
            $this->assertNotFalse($response);
            $this->assertArrayHasKey(0, $response);

            foreach ($response as $key => $page) {
                $this->assertInternalType(PHPUnit_IsType::TYPE_INT, $key);
                $this->assertGreaterThanOrEqual(1024, mb_strlen($page));
            }
        }

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);
    }

    public function testMergeWithTemplateFilename()
    {
        $returnFormats        = $this->getTestReturnFormats();

        $mergeData            = $this->getTestTemplateMergeData();
        $mergeSettings        = $this->getTestMergeSettings();

        $testTemplateFilename = $this->getTestTemplateFilename();

        $this->assertFileExists($testTemplateFilename);

        foreach ($returnFormats as $returnFormat) {

            $response = $this->reportingCloud->mergeDocument($mergeData, $returnFormat, null, $testTemplateFilename, false, $mergeSettings);

            $this->assertNotNull($response);
            $this->assertNotFalse($response);
            $this->assertArrayHasKey(0, $response);

            foreach ($response as $key => $page) {
                $this->assertInternalType(PHPUnit_IsType::TYPE_INT, $key);
                $this->assertGreaterThanOrEqual(1024, mb_strlen($page));
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidReturnFormat()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'X');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidTemplateName()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', '../invalid_template.tx');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidTemplateFilenameUnsupportedExtension()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', null, '/invalid/path/template.xxx');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidTemplateFilenameNoExtension()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', null, '/invalid/path/template');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidTemplateFilenameNoFile()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', null, '/invalid/path/template/');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidTemplateFilename()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', null, '/invalid/path/template.doc');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidMergeSettingsIntegerInsteadOfArray()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();
        $templateFilename   = $this->getTestTemplateFindAndReplaceFilename();

        $this->assertFileExists($templateFilename);

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', null, $templateFilename, 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidMergeSettingsStringInsteadOfBoolean()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();
        $mergeSettings      = $this->getTestMergeSettings();

        $templateFilename   = $this->getTestTemplateFindAndReplaceFilename();

        $this->assertFileExists($templateFilename);

        $mergeSettings['remove_empty_blocks'] = 'invalid';  // value must be boolean
        $mergeSettings['remove_empty_fields'] = 'invalid';
        $mergeSettings['remove_empty_images'] = 'invalid';

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', null, $templateFilename, $mergeSettings);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindAndReplaceInvalidMergeSettingsTimestampValues()
    {
        $findAndReplaceData = $this->getTestTemplateFindAndReplaceData();
        $mergeSettings      = $this->getTestMergeSettings();

        $templateFilename   = $this->getTestTemplateFindAndReplaceFilename();

        $this->assertFileExists($templateFilename);

        $mergeSettings['creation_date']          = -1;  // value must be timestamp
        $mergeSettings['last_modification_date'] = 'invalid';

        $this->reportingCloud->findAndReplace($findAndReplaceData, 'PDF', null, $templateFilename, $mergeSettings);
    }

    public function testFindAndReplaceWithTemplateName()
    {
        $returnFormats        = $this->getTestReturnFormats();

        $findAndReplaceData   = $this->getTestTemplateFindAndReplaceData();
        $mergeSettings        = $this->getTestMergeSettings();

        $testTemplateFilename = $this->getTestTemplateFindAndReplaceFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);

        foreach ($returnFormats as $returnFormat) {

            $response = $this->reportingCloud->findAndReplace($findAndReplaceData, $returnFormat, $tempTemplateName, null, $mergeSettings);

            $this->assertNotNull($response);
            $this->assertNotFalse($response);
            $this->assertGreaterThanOrEqual(1024, mb_strlen($response));
        }

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);
    }

    public function testFindAndReplaceWithTemplateFilename()
    {
        $returnFormats        = $this->getTestReturnFormats();

        $findAndReplaceData   = $this->getTestTemplateFindAndReplaceData();
        $mergeSettings        = $this->getTestMergeSettings();

        $testTemplateFilename = $this->getTestTemplateFindAndReplaceFilename();

        $this->assertFileExists($testTemplateFilename);

        foreach ($returnFormats as $returnFormat) {

            $response = $this->reportingCloud->findAndReplace($findAndReplaceData, $returnFormat, null, $testTemplateFilename, $mergeSettings);

            $this->assertNotNull($response);
            $this->assertNotFalse($response);
            $this->assertGreaterThanOrEqual(1024, mb_strlen($response));
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUploadTemplateInvalidTemplateFilenameUnsupportedExtension()
    {
        $this->reportingCloud->uploadTemplate('/invalid/path/document.xxx');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUploadTemplateInvalidTemplateFilenameNoExtension()
    {
        $this->reportingCloud->uploadTemplate('/invalid/path/document');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUploadTemplateInvalidTemplateFilenameNoFile()
    {
        $this->reportingCloud->uploadTemplate('/invalid/path/document/');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUploadTemplateInvalidTemplateFilename()
    {
        $this->reportingCloud->uploadTemplate('/invalid/path/template.doc');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDownloadTemplateInvalidTemplateName()
    {
        $templateFilename = $this->getTestTemplateFilename();   // should be templateName and not templateFilename

        $this->reportingCloud->downloadTemplate($templateFilename);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDeleteTemplateInvalidTemplateName()
    {
        $templateFilename = $this->getTestTemplateFilename();   // should be templateName and not templateFilename

        $this->reportingCloud->deleteTemplate($templateFilename);
    }

    public function testUploadDownloadDeleteTemplate()
    {
        $testTemplateFilename = $this->getTestTemplateFilename();
        $tempTemplateFilename = $this->getTempTemplateFilename();
        $tempTemplateName     = basename($tempTemplateFilename);

        $this->assertFileExists($testTemplateFilename);

        copy($testTemplateFilename, $tempTemplateFilename);

        $this->assertFileExists($tempTemplateFilename);

        $response = $this->reportingCloud->uploadTemplate($tempTemplateFilename);

        $this->assertTrue($response);

        $response = $this->reportingCloud->downloadTemplate($tempTemplateName);
        $responseLength = mb_strlen($response);

        $this->assertNotNull($response);
        $this->assertNotFalse($response);
        $this->assertGreaterThan(1024, $responseLength);

        $response = $this->reportingCloud->deleteTemplate($tempTemplateName);

        $this->assertTrue($response);

        unlink($tempTemplateFilename);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @expectedException RuntimeException
     */
    public function testRequestRuntimeException()
    {
        $this->reportingCloud->deleteTemplate('invalid-template.tx');
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function getTestReturnFormats()
    {
        $validator = new ReturnFormatValidator();

        $ret = $validator->getHaystack();

        return $ret;
    }

    protected function getTestTemplateFindAndReplaceData()
    {
        $ret = [
            [
                '%%FIELD1%%', 'hello field 1',
            ],
            [
                '%%FIELD2%%', 'hello field 2',
            ],
        ];

        return $ret;
    }

    protected function getTestTemplateMergeData()
    {
        $ret = [
            [
                'yourcompany_companyname'  => 'Text Control, LLC',
                'yourcompany_zip'          => '28226',
                'yourcompany_city'         => 'Charlotte',
                'yourcompany_street'       => '6926 Shannon Willow Rd, Suite 400',
                'yourcompany_phone'        => '704 544 7445',
                'yourcompany_fax'          => '704-542-0936',
                'yourcompany_url'          => 'www.textcontrol.com',
                'yourcompany_email'        => 'sales@textcontrol.com',

                'invoice_no'               => '778723',

                'billto_name'              => 'Joey Montana',
                'billto_companyname'       => 'Montana, LLC',
                'billto_customerid'        => '123',
                'billto_zip'               => '27878',
                'billto_city'              => 'Charlotte',
                'billto_street'            => '1 Washington Dr',
                'billto_phone'             => '887 267 3356',

                'payment_due'              => '20/1/2016',
                'payment_terms'            => 'NET 30',

                'salesperson_name'         => 'Mark Frontier',

                'delivery_date'            => '20/1/2016',
                'delivery_method'          => 'Ground',
                'delivery_method_terms'    => 'NET 30',

                'recipient_name'           => 'Joey Montana',
                'recipient_companyname'    => 'Montana, LLC',
                'recipient_zip'            => '27878',
                'recipient_city'           => 'Charlotte',
                'recipient_street'         => '1 Washington Dr',
                'recipient_phone'          => '887 267 3356',

                'total_discount'           => 532.60,
                'total_sub'                => 7673.4,
                'total_tax'                => 537.138,
                'total'                    => 8210.538,

                'item' => [
                    [
                        'qty'              => 1,
                        'item_no'          => 1,
                        'item_description' => 'Item description 1',
                        'item_unitprice'   => 2663,
                        'item_discount'    => 20,
                        'item_total'       => 2130.40,
                    ],
                    [
                        'qty'              => 1,
                        'item_no'          => 2,
                        'item_description' => 'Item description 2',
                        'item_unitprice'   => 5543,
                        'item_discount'    => 0,
                        'item_total'       => 5543,
                    ],
                ],
            ],
        ];

        // copy data 4 times
        // total record sets = 5

        for ($i = 0; $i < 4; $i++) {
            array_push($ret, $ret[0]);
        }

        return $ret;
    }


    protected function getTestMergeSettings()
    {
        $ret = [

            'creation_date'              => time(),
            'last_modification_date'     => time(),

            'remove_empty_blocks'        => true,
            'remove_empty_fields'        => true,
            'remove_empty_images'        => true,
            'remove_trailing_whitespace' => true,

            'author'                     => 'James Henry Trotter',
            'creator_application'        => 'The Giant Peach',
            'document_subject'           => 'The Old Green Grasshopper',
            'document_title'             => 'James and the Giant Peach',

            'user_password'              => '123456789',
        ];

        return $ret;
    }

    protected function getTestTemplateFilename()
    {
        $ret = sprintf('%s/test_template.tx', realpath(__DIR__ . '/TestAsset'));

        return $ret;
    }


    protected function getTempTemplateFilename()
    {
        $ret = sprintf('%s/test_template_%d.tx', sys_get_temp_dir(), rand(0, PHP_INT_MAX));

        return $ret;
    }

    protected function getTestDocumentFilename()
    {
        $ret = sprintf('%s/test_document.docx', realpath(__DIR__ . '/TestAsset'));

        return $ret;
    }

    protected function getTempDocumentFilename()
    {
        $ret = sprintf('%s/test_document_%d.docx', sys_get_temp_dir(), rand(0, PHP_INT_MAX));

        return $ret;
    }

    protected function getTestTemplateFindAndReplaceFilename()
    {
        $ret = sprintf('%s/test_find_and_replace.tx', realpath(__DIR__ . '/TestAsset'));

        return $ret;
    }

    // -----------------------------------------------------------------------------------------------------------------

}
