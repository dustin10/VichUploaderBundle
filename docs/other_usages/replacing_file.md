# Inject files from other sources

The bundle provides a way to inject files into entities coming from other sources than
[HTTP uploads via forms](../form/vich_file_type.md).

When you have configured your entity like laid out in the [usage page](../usage.md), you can use code like the one
below to inject files which are coming from other sources like

- already a file on the server,
- downloaded with curl/wget or
- migrate old uploads.

## Example

```php
// ...
use Acme\DemoBundle\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class MigrationCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
        parent::__construct();
    }
    // ...

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $product = new Product();
        $product->imageFile = new ReplacingFile('myFile.png');
        // ...

        $this->em->persist($product);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
