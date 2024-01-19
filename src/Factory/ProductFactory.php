<?php

namespace App\Factory;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Product>
 *
 * @method        Product|Proxy create(array|callable $attributes = [])
 * @method static Product|Proxy createOne(array $attributes = [])
 * @method static Product|Proxy find(object|array|mixed $criteria)
 * @method static Product|Proxy findOrCreate(array $attributes)
 * @method static Product|Proxy first(string $sortedField = 'id')
 * @method static Product|Proxy last(string $sortedField = 'id')
 * @method static Product|Proxy random(array $attributes = [])
 * @method static Product|Proxy randomOrCreate(array $attributes = [])
 * @method static ProductRepository|RepositoryProxy repository()
 * @method static Product[]|Proxy[] all()
 * @method static Product[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Product[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Product[]|Proxy[] findBy(array $attributes)
 * @method static Product[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Product[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class ProductFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        $product_name = self::faker()->text(50);

        return [
            'product_name' => $product_name,
            'sku' => $this->sku($product_name),
            'description' => self::faker()->text(100),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Product $product): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Product::class;
    }

    /**
	 * Generate a stock-keeping unit identifier.
     * 
	 * @see https://github.com/maetl/faker/blob/master/src/Fake/Product.php
	 * @param string $name name of the product to base the identifier
	 */
	private function sku($name=false) {
		if ($name) {
			$sku = substr(strtoupper(str_replace(array('a','e','i','o','u'), '', $name)), 0, 6);
		} else {
			$sku = $this->lexicalize(explode(',', 'b,c,d,f,g,h,j,k,l,m,n,p,q,r,s,t,v,w,x,y,z'), 3, 6, 'strtoupper');
		}
		return str_replace(' ', '', $sku) . "-" . rand(100,999);
	}
}
