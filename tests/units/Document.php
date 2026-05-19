<?php

namespace tests\units;

class Document extends \GLPITestCase {

   public function beforeTestMethod($method) {
      unset($_REQUEST['filename']);
      parent::beforeTestMethod($method);
   }

   public function afterTestMethod($method) {
      unset($_REQUEST['filename']);
      parent::afterTestMethod($method);
   }

   public function testPrepareInputForAddFiltersRequestControlledFileFields() {
      $_REQUEST['filename'] = 'evil.php';

      $input = [
         'name'     => 'legit name',
         'filename' => 'evil.php',
         'filepath' => '_dumps/dump.sql',
         'sha1sum'  => 'bad',
      ];

      $prepared = (new \Document())->prepareInputForAdd($input);

      $this->array($prepared)
         ->notHasKeys(['filename', 'filepath', 'sha1sum'])
         ->hasKey('current_filename');
      $this->string($prepared['name'])->isIdenticalTo('legit name');
      $this->string($prepared['current_filename'])->isIdenticalTo('');
   }

   public function testPrepareInputForUpdateFiltersRequestControlledFileFields() {
      $_REQUEST['filename'] = 'evil.php';

      $document = new \Document();
      $document->fields = [
         'filename' => 'current.txt',
         'filepath' => 'TXT/current.txt',
      ];

      $input = [
         'id'               => 1,
         'name'             => 'legit name',
         'filename'         => 'evil.php',
         'filepath'         => '_dumps/dump.sql',
         'sha1sum'          => 'bad',
         'current_filepath' => '_dumps/other.sql',
         'current_filename' => 'other.sql',
      ];

      $prepared = $document->prepareInputForUpdate($input);

      $this->array($prepared)
         ->notHasKeys(['filename', 'filepath', 'sha1sum', 'current_filepath', 'current_filename']);
      $this->string($prepared['name'])->isIdenticalTo('legit name');
   }

   public function testSetFileCopySourceAllowsInternalDocumentCopy() {
      $source = new \Document();
      $source->fields = [
         'filename' => 'source.txt',
         'filepath' => 'TXT/source.txt',
         'sha1sum'  => 'source-sha1',
         'mime'     => 'text/plain',
      ];

      $document = new \Document();
      $document->setFileCopySource($source);

      $prepared = $document->prepareInputForAdd([
         'name'     => 'copy',
         'filepath' => '_dumps/dump.sql',
         'sha1sum'  => 'bad',
      ]);

      $this->string($prepared['filename'])->isIdenticalTo('source.txt');
      $this->string($prepared['filepath'])->isIdenticalTo('TXT/source.txt');
      $this->string($prepared['sha1sum'])->isIdenticalTo('source-sha1');
      $this->string($prepared['mime'])->isIdenticalTo('text/plain');
   }
}
