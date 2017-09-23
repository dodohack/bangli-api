<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AffiliateWindowTest extends TestCase
{
    /**
     * Test AWIN API and related.
     *
     * @return void
     */

    public function testAwinMerchantMetadataAPICorrectness()
    {
        $this->assertEquals(env('AWIN_ADVERTISER_API'),
            'https://ui2.awin.com/affiliates/shopwindow/datafeed_metadata.php');
    }

    public function testAwinPromotionAPICorrectness()
    {
        $this->assertEquals(env('AWIN_PROMOTION_API'),
            'https://ui.awin.com/export-promotions');
    }

    public function testAwinIDandPWDNotNull()
    {
        $this->assertNotNull(env('AWIN_ID'));
        $this->assertNotNull(env('AWIN_PROMOTION_ID'));
        $this->assertNotNull(env('AWIN_ADVERTISER_API_PWD'));
    }

    /* Test AWIN perchant API endpoint is responsible and returns data */
    public function testRetrieveAwinMerchantMetadata()
    {
        $this->get('/awin-get-merchants');

        $this->assertEquals(
            'SUCCESS', $this->response->getContent()
        );
    }

    /* Test AWIN promotion API endpoint is responsible and returns data */
    public function testRetrieveAwinPromotions()
    {
        $this->get('/awin-get-offers');
        $this->assertEquals(
            'SUCCESS', $this->response->getContent()
        );
    }

    public function testAddSingleMerchantIntoDatabase()
    {
        $awin = new \App\Http\Controllers\Backend\AffiliateWindowController();
        $metadata = [
            '2186',
            'Thorntons',
            'https://ui2.awin.com/logos/2186/logo.gif',
            'yes',
            '3',
            'Thorntons.co.uk - delicious chocolate delivered',
            'Thorntons.co.uk offers a unique and exciting gift delivery service. From favourite chocolate collections to hampers bursting with treats, there&#39;s something for everyone in this online chocolate shop.   Customers will find a fantastic range of gifts that aren&#39;t available in Thorntons high-street stores. Including wine and Champagne, ceramic gifts and a great selection of sweets.   Next day delivery is available and Thorntons also offers an international gift delivery service, ensuring that everyone can receive their favourite chocolates no matter where they are in the world!',
            'http://www.awin1.com/awclick.php?mid=2186&id=210595',
            'Food',
            'no',
            '672',
            '0000-00-00 00:00:00',
            '0000-00-00 00:00:00',
            '0',
            'http://www.thorntons.co.uk/special-offers',
            'GB'
        ];

        $this->assertEquals(
          'SUCCESS',  $awin->testPostMerchant($metadata)->getContent()
        );
    }

    /* Test if we have updated any merchant metadata in database */
    public function testAddMerchantsIntoDatabase()
    {
        $this->get('/awin-update-merchants');
        $this->assertGreaterThan(
            0, $this->response->getContent()
        );
    }

    /* Test add single offer into database */
    public function testAddSingleOfferIntoDatabase()
    {
        $awin = new \App\Http\Controllers\Backend\AffiliateWindowController();
        $input = [
            '224991',
            'lookfantastic UK',
            '2082',
            'Vouchers Only',
            'LFKERA15',
            '15% off Kerastase + an extra 15%',
            '12/09/2017 11:53',
            '19/09/2017 11:52',
            'Haircare Products',
            'Ireland,United Kingdom',
            'Exclusions Apply',
            'http://www.awin1.com/cread.php?awinaffid=210595&awinmid=2082&p=https%3A%2F%2Fwww.lookfantastic.com%2Fbrands%2Fkerastase.list',
            'https://www.lookfantastic.com/brands/kerastase.list',
            'No',
            '12/09/2017 11:53'
        ];

        $this->assertEquals(
          'SUCCESS', $awin->testPostOffer($input)->getContent()
        );
    }

    /* Test if we have updated any offers in database */
    public function testAddOffersIntoDatabase()
    {
        $this->get('/awin-update-offers');
        $this->assertGreaterThan(
            0, $this->response->getContent()
        );
    }

}
