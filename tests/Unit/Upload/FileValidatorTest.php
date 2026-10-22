<?php

declare(strict_types=1);

namespace Buffalo\Tests\Unit\Upload;

use Buffalo\Upload\FileValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileValidator::class)]
final class FileValidatorTest extends TestCase
{
    private FileValidator $validator;

    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        $this->validator = new FileValidator();
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }
    }

    public function testAcceptsARealGif(): void
    {
        $path = $this->givenGif();

        self::assertNull($this->validator->reasonToReject(
            $path,
            'foto.gif',
            filesize($path) ?: 0,
            FileValidator::IMAGE_EXTENSIONS,
        ));
    }

    /**
     * The hole this class exists to close: the legacy gate read the declared
     * content type, which the caller picks, and kept the submitted filename.
     * A script announced as an image landed in the web root executable.
     */
    public function testRejectsAScriptWhateverItClaimsToBe(): void
    {
        $path = $this->givenFile("<?php echo 'pwned';");

        self::assertSame(
            'No es un archivo valido.',
            $this->validator->reasonToReject($path, 'shell.php', 20, FileValidator::IMAGE_EXTENSIONS),
        );
    }

    public function testRejectsAScriptRenamedToLookLikeAnImage(): void
    {
        // Passes the extension check, fails on its bytes.
        $path = $this->givenFile("<?php echo 'pwned';");

        self::assertSame(
            'El contenido no corresponde a la extensión.',
            $this->validator->reasonToReject($path, 'shell.gif', 20, FileValidator::IMAGE_EXTENSIONS),
        );
    }

    public function testRejectsAnImageWhoseExtensionNamesTheWrongFormat(): void
    {
        $path = $this->givenGif();

        self::assertSame(
            'El contenido no corresponde a la extensión.',
            $this->validator->reasonToReject(
                $path,
                'foto.png',
                filesize($path) ?: 0,
                FileValidator::IMAGE_EXTENSIONS,
            ),
        );
    }

    public function testRejectsAnImageWhereAPdfIsExpected(): void
    {
        $path = $this->givenGif();

        self::assertSame(
            'No es un archivo valido.',
            $this->validator->reasonToReject(
                $path,
                'foto.gif',
                filesize($path) ?: 0,
                FileValidator::PDF_EXTENSIONS,
            ),
        );
    }

    public function testAcceptsAPdfByItsMagicBytes(): void
    {
        $path = $this->givenFile("%PDF-1.4\n% fake but correctly headed");

        self::assertNull($this->validator->reasonToReject(
            $path,
            'ficha.pdf',
            100,
            FileValidator::PDF_EXTENSIONS,
        ));
    }

    public function testRejectsSomethingOnlyCalledPdf(): void
    {
        $path = $this->givenFile('not a pdf at all');

        self::assertSame(
            'El contenido no corresponde a la extensión.',
            $this->validator->reasonToReject($path, 'ficha.pdf', 16, FileValidator::PDF_EXTENSIONS),
        );
    }

    public function testRejectsAnOversizedFileBeforeLookingAtIt(): void
    {
        self::assertSame(
            'El archivo supera el tamaño permitido.',
            $this->validator->reasonToReject(
                '/does/not/exist',
                'foto.gif',
                FileValidator::MAX_BYTES,
                FileValidator::IMAGE_EXTENSIONS,
            ),
        );
    }

    public function testRejectsAFileWithNoExtension(): void
    {
        $path = $this->givenGif();

        self::assertSame(
            'No es un archivo valido.',
            $this->validator->reasonToReject($path, 'sinextension', 100, FileValidator::IMAGE_EXTENSIONS),
        );
    }

    public function testTheExtensionCheckIgnoresCase(): void
    {
        $path = $this->givenGif();

        self::assertNull($this->validator->reasonToReject(
            $path,
            'FOTO.GIF',
            filesize($path) ?: 0,
            FileValidator::IMAGE_EXTENSIONS,
        ));
    }

    public function testStoredNamesKeepTheExtensionButNeverTheSubmittedName(): void
    {
        $name = FileValidator::safeName('../../evil shell.gif', 'gif');

        self::assertStringEndsWith('.gif', $name);
        self::assertStringNotContainsString('/', $name);
        self::assertStringNotContainsString('..', $name);
        self::assertStringNotContainsString(' ', $name);
        self::assertStringStartsWith('evil_shell_', $name);
    }

    public function testTwoUploadsOfTheSameNameDoNotCollide(): void
    {
        self::assertNotSame(
            FileValidator::safeName('foto.gif', 'gif'),
            FileValidator::safeName('foto.gif', 'gif'),
        );
    }

    public function testAnUnnameableFileStillGetsAName(): void
    {
        self::assertStringStartsWith('archivo_', FileValidator::safeName('???.gif', 'gif'));
    }

    private function givenGif(): string
    {
        // Smallest valid GIF: getimagesize() parses this header.
        return $this->givenFile(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));
    }

    private function givenFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        self::assertIsString($path);
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
