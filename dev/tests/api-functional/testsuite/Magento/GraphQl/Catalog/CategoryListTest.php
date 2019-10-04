<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test CategoryList GraphQl query
 */
class CategoryListTest extends GraphQlAbstract
{

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @dataProvider filterSingleCategoryDataProvider
     * @param $field
     * @param $condition
     * @param $value
     */
    public function testFilterSingleCategoryByField($field, $condition, $value, $expectedResult)
    {
        $query = <<<QUERY
{
    categoryList(filters: { $field : { $condition : "$value" } }){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $this->assertResponseFields($result['categoryList'][0], $expectedResult);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @dataProvider filterMultipleCategoriesDataProvider
     * @param $field
     * @param $condition
     * @param $value
     * @param $expectedResult
     */
    public function testFilterMultipleCategoriesByField($field, $condition, $value, $expectedResult)
    {
        $query = <<<QUERY
{
    categoryList(filters: { $field : { $condition : $value } }){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(count($expectedResult), $result['categoryList']);
        foreach ($expectedResult as $i => $expected) {
            $this->assertResponseFields($result['categoryList'][$i], $expected);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterCategoryByMultipleFields()
    {
        $query = <<<QUERY
{
    categoryList(filters: {ids: {in: ["6","7","8","9","10"]}, name: {match: "Movable"}}){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(3, $result['categoryList']);

        $expectedCategories = [7 => 'Movable', 9 => 'Movable Position 1', 10 => 'Movable Position 2'];
        $actualCategories = array_column($result['categoryList'], 'name', 'id');
        $this->assertEquals($expectedCategories, $actualCategories);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterWithInactiveCategory()
    {
        $query = <<<QUERY
{
    categoryList(filters: {url_key: {in: ["inactive", "category-2"]}}){
        id
        name
        url_key
        url_path
    }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $actualCategories = array_column($result['categoryList'], 'url_key', 'id');
        $this->assertContains('category-2', $actualCategories);
        $this->assertNotContains('inactive', $actualCategories);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testQueryChildCategoriesWithProducts()
    {
        $query =  <<<QUERY
{
    categoryList(filters: {ids: {in: ["3"]}}){
        id
        name
        url_key
        url_path
        description
        products{
          total_count
          items{
            name
            sku
          }
        }
        children{
          name
          url_key
          description
          products{
            total_count
            items{
              name
              sku
            }
          }
          children{
            name
          }
        }
    }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $a = 3;

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $baseCategory = $result['categoryList'][0];

        $this->assertEquals('Category 1', $baseCategory['name']);
        $this->assertArrayHasKey('products', $baseCategory);
        //Check base category products
        $expectedBaseCategoryProducts = [
            ['sku' => 'simple', 'name' => 'Simple Product'],
            ['sku' => '12345', 'name' => 'Simple Product Two'],
            ['sku' => 'simple-4', 'name' => 'Simple Product Three']
        ];
        $this->assertCategoryProducts($baseCategory, $expectedBaseCategoryProducts);
        //Check base category children
        $expectedBaseCategoryChildren = [
            ['name' => 'Category 1.1', 'description' => 'Category 1.1 description.'],
            ['name' => 'Category 1.2', 'description' => 'Its a description of Test Category 1.2']
        ];
        $this->assertCategoryChildren($baseCategory, $expectedBaseCategoryChildren);

        //Check first child category
        $firstChildCategory = $baseCategory['children'][0];
        $this->assertEquals('Category 1.1', $firstChildCategory['name']);
        $this->assertEquals('Category 1.1 description.', $firstChildCategory['description']);
        $firstChildCategoryExpectedProducts = [
            ['sku' => 'simple', 'name' => 'Simple Product'],
            ['sku' => '12345', 'name' => 'Simple Product Two'],
        ];
        $this->assertCategoryProducts($firstChildCategory, $firstChildCategoryExpectedProducts);
        $firstChildCategoryChildren = [['name' =>'Category 1.1.1']];
        $this->assertCategoryChildren($firstChildCategory, $firstChildCategoryChildren);
        //Check second child category
        $secondChildCategory = $baseCategory['children'][1];
        $this->assertEquals('Category 1.2', $secondChildCategory['name']);
        $this->assertEquals('Its a description of Test Category 1.2', $secondChildCategory['description']);
        $firstChildCategoryExpectedProducts = [
            ['sku' => 'simple', 'name' => 'Simple Product'],
            ['sku' => 'simple-4', 'name' => 'Simple Product Three']
        ];
        $this->assertCategoryProducts($secondChildCategory, $firstChildCategoryExpectedProducts);
        $firstChildCategoryChildren = [];
        $this->assertCategoryChildren($secondChildCategory, $firstChildCategoryChildren);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testNoResultsFound()
    {
        $query = <<<QUERY
{
    categoryList(filters: {url_key: {in: ["inactive", "does-not-exist"]}}){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('categoryList', $result);
        $this->assertEquals([], $result['categoryList']);
    }

    /**
     * @return array
     */
    public function filterSingleCategoryDataProvider(): array
    {
        return [
            [
                'ids',
                'eq',
                '4',
                [
                    'id' => '4',
                    'name' => 'Category 1.1',
                    'url_key' => 'category-1-1',
                    'url_path' => 'category-1/category-1-1',
                    'children_count' => '0',
                    'path' => '1/2/3/4',
                    'position' => '1'
                ]
            ],
            [
                'name',
                'match',
                'Movable Position 2',
                [
                    'id' => '10',
                    'name' => 'Movable Position 2',
                    'url_key' => 'movable-position-2',
                    'url_path' => 'movable-position-2',
                    'children_count' => '0',
                    'path' => '1/2/10',
                    'position' => '6'
                ]
            ],
            [
                'url_key',
                'eq',
                'category-1-1-1',
                [
                    'id' => '5',
                    'name' => 'Category 1.1.1',
                    'url_key' => 'category-1-1-1',
                    'url_path' => 'category-1/category-1-1/category-1-1-1',
                    'children_count' => '0',
                    'path' => '1/2/3/4/5',
                    'position' => '1'
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function filterMultipleCategoriesDataProvider(): array
    {
        return[
            //Filter by multiple IDs
            [
                'ids',
                'in',
                '["4", "9", "10"]',
                [
                    [
                        'id' => '4',
                        'name' => 'Category 1.1',
                        'url_key' => 'category-1-1',
                        'url_path' => 'category-1/category-1-1',
                        'children_count' => '0',
                        'path' => '1/2/3/4',
                        'position' => '1'
                    ],
                    [
                        'id' => '9',
                        'name' => 'Movable Position 1',
                        'url_key' => 'movable-position-1',
                        'url_path' => 'movable-position-1',
                        'children_count' => '0',
                        'path' => '1/2/9',
                        'position' => '5'
                    ],
                    [
                        'id' => '10',
                        'name' => 'Movable Position 2',
                        'url_key' => 'movable-position-2',
                        'url_path' => 'movable-position-2',
                        'children_count' => '0',
                        'path' => '1/2/10',
                        'position' => '6'
                    ]
                ]
            ],
            //Filter by multiple url keys
            [
                'url_key',
                'in',
                '["category-1-2", "movable"]',
                [
                    [
                        'id' => '7',
                        'name' => 'Movable',
                        'url_key' => 'movable',
                        'url_path' => 'movable',
                        'children_count' => '0',
                        'path' => '1/2/7',
                        'position' => '3'
                    ],
                    [
                        'id' => '13',
                        'name' => 'Category 1.2',
                        'url_key' => 'category-1-2',
                        'url_path' => 'category-1/category-1-2',
                        'children_count' => '0',
                        'path' => '1/2/3/13',
                        'position' => '2'
                    ]
                ]
            ],
            //Filter by matching multiple names
            [
                'name',
                'match',
                '"Position"',
                [
                    [
                        'id' => '9',
                        'name' => 'Movable Position 1',
                        'url_key' => 'movable-position-1',
                        'url_path' => 'movable-position-1',
                        'children_count' => '0',
                        'path' => '1/2/9',
                        'position' => '5'
                    ],
                    [
                        'id' => '10',
                        'name' => 'Movable Position 2',
                        'url_key' => 'movable-position-2',
                        'url_path' => 'movable-position-2',
                        'children_count' => '0',
                        'path' => '1/2/10',
                        'position' => '6'
                    ],
                    [
                        'id' => '11',
                        'name' => 'Movable Position 3',
                        'url_key' => 'movable-position-3',
                        'url_path' => 'movable-position-3',
                        'children_count' => '0',
                        'path' => '1/2/11',
                        'position' => '7'
                    ]
                ]
            ]
        ];
    }

    /**
     * Check category products
     *
     * @param array $category
     * @param array $expectedProducts
     */
    private function assertCategoryProducts(array $category, array $expectedProducts)
    {
        $this->assertEquals(count($expectedProducts), $category['products']['total_count']);
        $this->assertCount(count($expectedProducts), $category['products']['items']);
        $this->assertResponseFields($category['products']['items'], $expectedProducts);
    }

    /**
     * Check category child categories
     *
     * @param array $category
     * @param array $expectedChildren
     */
    private function assertCategoryChildren(array $category, array $expectedChildren)
    {
        $this->assertArrayHasKey('children', $category);
        $this->assertCount(count($expectedChildren), $category['children']);
        foreach ($expectedChildren as $i => $expectedChild) {
            $this->assertResponseFields($category['children'][$i], $expectedChild);
        }
    }
}
