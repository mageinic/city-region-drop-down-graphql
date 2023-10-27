<?php
/**
 * MageINIC
 * Copyright (C) 2023 MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_CityRegionPostcodeGraphQl
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\CityRegionPostcodeGraphQl\Model\Resolver;

use MageINIC\CityRegionPostcode\Api\CityRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use MageINIC\CityRegionPostcode\Helper\Data;

/**
 * Resolver fetches the data and formats it according to the GraphQL schema.
 *
 */
class City implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    public SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var CityRepositoryInterface
     */
    private CityRepositoryInterface $cityRepository;
    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;
    /**
     * @var ServiceOutputProcessor
     */
    private ServiceOutputProcessor $serviceOutputProcessor;
    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CityRepositoryInterface $cityRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ServiceOutputProcessor $serviceOutputProcessor
     */
    public function __construct(
        SearchCriteriaBuilder   $searchCriteriaBuilder,
        CityRepositoryInterface $cityRepository,
        SortOrderBuilder        $sortOrderBuilder,
        ServiceOutputProcessor  $serviceOutputProcessor,
        Data $helperData
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cityRepository = $cityRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->helperData = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $this->validateArgs($args);
            (int)$context->getExtensionAttributes()->getStore()->getId();
            $searchCriteria = $this->searchCriteriaBuilder->build($field->getName(), $args);
            $searchCriteria->setCurrentPage($args['currentPage']);
            $searchCriteria->setPageSize($args['pageSize']);
            if (isset($args['sort'])) {
                $sort = $args['sort'];
                foreach ($sort as $key => $value) {
                    $sortOrder = $this->sortOrderBuilder->setField($key)->setDirection($value)->create();
                    $searchCriteria->setSortOrders([$sortOrder]);
                }
            }
            $searchResult = $this->cityRepository->getList($searchCriteria);
            $postData = [
                "items" => [],
                "total_count" => 0
            ];
            if ($this->helperData->isActive() && $this->helperData->isCityActive())
            {
                foreach ($searchResult->getItems() as $city) {
                    $customerData = $this->serviceOutputProcessor->process(
                        $city,
                        CityRepositoryInterface::class,
                        'getById'
                    );
                    if (!empty($city->getName())) {
                        $customerData['default_name'] = $city->getName();
                    }
                    $postData["items"][] = $customerData;
                }
                $postData['total_count'] = $searchResult->getTotalCount();
            }
            return $postData;
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }

    /**
     * Validate Arguments
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args): void
    {
        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
    }
}
