<?php

use Clapp\ImageRepository\ImageRepository;
use League\Flysystem\Memory\MemoryAdapter;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Clapp\ImageRepository\ImageMissingOrInvalidException;

class ImageRepositoryTest extends TestCase{

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingStorageDisk(){
        $repo = new ImageRepository();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingCacheDisks(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk);
    }

    public function testSuccessfulCreation(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);

        $this->assertSame($repo->getStorageDisk(), $storageDisk);
        $this->assertSame($repo->getCacheDisk(), $cacheDisk);

        $this->assertNotSame($repo->getStorageDisk(), $cacheDisk);
        $this->assertNotSame($repo->getCacheDisk(), $storageDisk);
    }

    public function testGetterSetter(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $storageDisk2 = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk2 = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);

        $this->assertSame($repo->getStorageDisk(), $storageDisk);
        $this->assertSame($repo->getCacheDisk(), $cacheDisk);

        $repo->setStorageDisk($storageDisk2);
        $this->assertSame($repo->getStorageDisk(), $storageDisk2);
        $this->assertNotSame($repo->getStorageDisk(), $storageDisk);

        $repo->setCacheDisk($cacheDisk2);
        $this->assertSame($repo->getCacheDisk(), $cacheDisk2);
        $this->assertNotSame($repo->getCacheDisk(), $cacheDisk);

        $this->assertNotNull($repo->getImageManager());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPutNoFile(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);

        $repo->put("");
    }

    public function testPutValidFile(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $key = $repo->put($this->getDummyImage());
        $this->assertNotEmpty($key);

        return [
            'storageDisk' => $repo->getStorageDisk(),
            'cacheDisk' => $repo->getCacheDisk(),
            'key' => $key,
        ];
    }
    public function testPutValidPngFile(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $key = $repo->put($this->getDummyImage("png"));
        $this->assertNotEmpty($key);

        return [
            'storageDisk' => $repo->getStorageDisk(),
            'cacheDisk' => $repo->getCacheDisk(),
            'key' => $key,
        ];
    }

    public function testPutValidGifFile(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $key = $repo->put($this->getDummyImage("gif"));
        $this->assertNotEmpty($key);

        return [
            'storageDisk' => $repo->getStorageDisk(),
            'cacheDisk' => $repo->getCacheDisk(),
            'key' => $key,
        ];
    }
    /**
     * @expectedException Clapp\ImageRepository\ImageMissingOrInvalidException
     */
    public function testGetInvalidFile(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);

        $repo->get("invalidkey");
    }
    /**
     * @expectedException Clapp\ImageRepository\ImageMissingOrInvalidException
     * @depends testPutValidFile
     */
    public function testGetValidFileFromWrongPrefix($data){
        $storageDisk = $data['storageDisk'];
        $cacheDisk = $data['cacheDisk'];
        $key = $data['key'];
        $wrongPrefix = "foo-images";
        $repo = new ImageRepository($wrongPrefix, $storageDisk, $cacheDisk);

        $repo->get($key);
    }
    /**
     * @depends testPutValidFile
     */
    public function testGetValidFileFromRightPrefix($data){
        $storageDisk = $data['storageDisk'];
        $cacheDisk = $data['cacheDisk'];
        $key = $data['key'];
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $image = $repo->get($key);
        $this->assertNotEmpty($image);
    }
    /**
     * @expectedException Exception
     */
    public function testRemove(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);
        $repo->remove('somekey');
    }
    /**
     * @expectedException Exception
     */
    public function testFlush(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);
        $repo->flush();
    }
    public function testThumbnail(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);

        $image = $repo->get(__DIR__ . "/cat.jpg");
        $this->assertNotEmpty($image);
    }
    /**
     * @expectedException Clapp\ImageRepository\ImageMissingOrInvalidException
     */
    public function testThumbnailMissingFile(){
        $storageDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $cacheDisk = new FilesystemAdapter(new Filesystem(new MemoryAdapter()));
        $repo = new ImageRepository("", $storageDisk, $cacheDisk);

        $image = $repo->get(__DIR__ . "/missingfile.jpg");
        $this->assertNotEmpty($image);
    }
    /**
     * @depends testPutValidFile
     * @expectedException InvalidArgumentException
     */
    public function testCustomTransformMissingTransformIdFunction($data){
        $storageDisk = $data['storageDisk'];
        $cacheDisk = $data['cacheDisk'];
        $key = $data['key'];
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $image = $repo->get($key, function($image){

        });
        $this->assertNotEmpty($image);
    }
    /**
     * @depends testPutValidFile
     * @expectedException InvalidArgumentException
     */
    public function testCustomTransformInvalidTransformIdFunction($data){
        $storageDisk = $data['storageDisk'];
        $cacheDisk = $data['cacheDisk'];
        $key = $data['key'];
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $image = $repo->get($key, function($image){

        },function(){

        });
        $this->assertNotEmpty($image);
    }
    /**
     * @depends testPutValidFile
     */
    public function testCustomTransform($data){
        $storageDisk = $data['storageDisk'];
        $cacheDisk = $data['cacheDisk'];
        $key = $data['key'];
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $image = $repo->get($key, function($image){

            $image->resize(500, 500, function($constraint){
                $constraint->aspectRatio();
            });

            return $image;
        }, function(){
            return "foo";
        });
        $this->assertNotEmpty($image);
    }
    /**
     * @depends testPutValidFile
     */
    public function testCustomTransformCorrectSize($data){
        $storageDisk = $data['storageDisk'];
        $cacheDisk = $data['cacheDisk'];
        $key = $data['key'];
        $rightPrefix = "profile-images";
        $repo = new ImageRepository($rightPrefix, $storageDisk, $cacheDisk);

        $image = $repo->get($key, function($image){

            $image->resize(123, 321, function($constraint){
                $constraint->aspectRatio();
            });

            return $image;
        }, function(){
            return "123x123";
        });
        $this->assertNotEmpty($image);

        $img = (new \Intervention\Image\ImageManager(array('driver' => 'gd')))->make($cacheDisk->get($image));
        $this->assertEquals(123, $img->width());
        $this->assertEquals(123, $img->height());
    }


}
