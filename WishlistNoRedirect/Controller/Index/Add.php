<?php
namespace DoAgency\WishlistNoRedirect\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;


class Add extends \Magento\Wishlist\Controller\AbstractIndex
{
    protected $wishlistProvider;
    protected $_customerSession;
    protected $productRepository;
    protected $formKeyValidator;
    protected $_urlInterface2;

    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Framework\App\Response\RedirectInterface $_urlInterface,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator
        ) {
            $this->_customerSession = $customerSession;
            $this->wishlistProvider = $wishlistProvider;
            $this->productRepository = $productRepository;
            $this->formKeyValidator = $formKeyValidator;
            $this->_urlInterface2 = $_urlInterface;
            parent::__construct($context);
    }
    

    public function execute()
    {
        //DOA - $url is used in the end of the file
        $url = $this->_urlInterface2->getRefererUrl();
        
        
        // @var \Magento\Framework\Controller\Result\Redirect $resultRedirect 
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/');
        }
        
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }
        
        $session = $this->_customerSession;
        
        $requestParams = $this->getRequest()->getParams();
        
        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->unsBeforeWishlistRequest();
        }
        
        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;
        if (!$productId) {
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }
        
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        
        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }
        
        try {
            $buyRequest = new \Magento\Framework\DataObject($requestParams);
            
            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
            $wishlist->save();
            
            $this->_eventManager->dispatch(
                'wishlist_add_product',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                );
            
            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
                $referer = $this->_redirect->getRefererUrl();
            }
            
            $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();
            
            $this->messageManager->addComplexSuccessMessage(
                'addProductSuccessMessage',
                [
                    'product_name' => $product->getName(),
                    'referer' => $referer
                ]
                );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
                );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to Wish List right now.')
                );
        }
        
        $resultRedirect->setPath($url);
        return $resultRedirect;
    }
}
