=====================================
Rule ``file_docblock_before_declare``
=====================================

A file-level docblock placed after ``declare`` statements must be moved before
them, as required by PSR-12.

Examples
--------

Example #1
~~~~~~~~~~

.. code-block:: diff

   --- Original
   +++ New
    <?php

   -declare(strict_types=1);
   -
    /**
     * Bootstrap for the application.
     */
   +
   +declare(strict_types=1);

    namespace App;

References
----------

- Fixer class: `PhpCsFixer\\Fixer\\Comment\\FileDocblockBeforeDeclareFixer <./../../../src/Fixer/Comment/FileDocblockBeforeDeclareFixer.php>`_
- Test class: `PhpCsFixer\\Tests\\Fixer\\Comment\\FileDocblockBeforeDeclareFixerTest <./../../../tests/Fixer/Comment/FileDocblockBeforeDeclareFixerTest.php>`_

The test class defines officially supported behaviour. Each test case is a part of our backward compatibility promise.
