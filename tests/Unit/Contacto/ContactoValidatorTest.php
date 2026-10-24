<?php

declare(strict_types=1);

namespace Buffalo\Tests\Unit\Contacto;

use Buffalo\Contacto\ContactoValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactoValidator::class)]
final class ContactoValidatorTest extends TestCase
{
    private ContactoValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ContactoValidator();
    }

    /** @param array<string, string> $cambios */
    private function mensaje(array $cambios = []): array
    {
        return $cambios + [
            'nombre' => 'César Díaz',
            'email' => 'cesar@example.pe',
            'telefono' => '+51 999 888 777',
            'direccion' => 'Av. Javier Prado 123',
            'ciudad' => 'Lima',
            'comentarios' => 'Quisiera información sobre la B15.',
        ];
    }

    public function testACompleteMessageIsAccepted(): void
    {
        self::assertSame([], $this->validator->errores($this->mensaje()));
    }

    public function testTheOptionalFieldsMayBeLeftEmpty(): void
    {
        $errores = $this->validator->errores($this->mensaje([
            'telefono' => '',
            'direccion' => '',
            'ciudad' => '',
        ]));

        self::assertSame([], $errores);
    }

    public function testNameEmailAndCommentsAreRequired(): void
    {
        $errores = $this->validator->errores([
            'nombre' => '',
            'email' => '',
            'comentarios' => '',
        ]);

        self::assertSame(['nombre', 'email', 'comentarios'], array_keys($errores));
        self::assertSame('Ingrese su nombre.', $errores['nombre']);
    }

    public function testWhitespaceIsNotAValue(): void
    {
        $errores = $this->validator->errores($this->mensaje(['nombre' => "   \n\t "]));

        self::assertArrayHasKey('nombre', $errores);
    }

    public function testAMalformedEmailIsRejected(): void
    {
        foreach (['cesar', 'cesar@', '@example.pe', 'cesar example@pe'] as $email) {
            $errores = $this->validator->errores($this->mensaje(['email' => $email]));

            self::assertArrayHasKey('email', $errores, "{$email} should not be accepted");
            self::assertSame('El correo no parece válido.', $errores['email']);
        }
    }

    public function testALetterInThePhoneIsRejected(): void
    {
        $errores = $this->validator->errores($this->mensaje(['telefono' => '999-LLAME-YA']));

        self::assertArrayHasKey('telefono', $errores);
    }

    public function testThePhoneMayCarryTheUsualPunctuation(): void
    {
        foreach (['+51 1 4567890', '(01) 456-7890', '456.7890'] as $telefono) {
            $errores = $this->validator->errores($this->mensaje(['telefono' => $telefono]));

            self::assertSame([], $errores, "{$telefono} should be accepted");
        }
    }

    public function testAPhoneTooShortToBeOneIsRejected(): void
    {
        $errores = $this->validator->errores($this->mensaje(['telefono' => '12345']));

        self::assertArrayHasKey('telefono', $errores);
    }

    public function testAFieldAtItsLimitIsAccepted(): void
    {
        $limite = ContactoValidator::LIMITES['nombre'];

        $errores = $this->validator->errores($this->mensaje([
            'nombre' => str_repeat('á', $limite),
        ]));

        // The accents are the point: these are 100 characters and 200 bytes,
        // and the column counts characters.
        self::assertSame([], $errores);
    }

    public function testAFieldOverItsLimitIsRejected(): void
    {
        $limite = ContactoValidator::LIMITES['nombre'];

        $errores = $this->validator->errores($this->mensaje([
            'nombre' => str_repeat('á', $limite + 1),
        ]));

        self::assertSame('El nombre no puede pasar de 100 caracteres.', $errores['nombre']);
    }

    public function testEveryFieldHasALimit(): void
    {
        // The template reads LIMITES for its maxlength attributes, so a field
        // missing from it would be unbounded in the browser and truncated by
        // the column.
        self::assertSame(
            ['nombre', 'email', 'telefono', 'direccion', 'ciudad', 'comentarios'],
            ContactoValidator::campos(),
        );
    }

    public function testAMissingKeyIsTreatedAsEmpty(): void
    {
        $errores = $this->validator->errores([]);

        self::assertSame(['nombre', 'email', 'comentarios'], array_keys($errores));
    }
}
