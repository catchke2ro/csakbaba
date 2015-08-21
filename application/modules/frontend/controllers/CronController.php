<?php

class CronController extends CB_Controller_Action {


	public function cronAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);
		$productModel=new \CB\Model\Product();
		$userModel=new \CB\Model\User();
		$productModel->initQb();
		$productModel->qb->field('status')->in(array(1));
		$productModel->qb->field('date_period')->lte(new DateTime('-30 days'));
		$periodProduct=$productModel->runQuery();
		foreach($periodProduct as $pp){
			if($pp->autorenew=='always' || $pp->autorenew=='once'){
				CB_Resource_Functions::logEvent('productAutorenew', array('product'=>$pp));
				if($pp->user && ($pp->user->balance > Zend_Registry::get('uploadPrice'))){
					$pp->user->balance=$pp->user->balance - Zend_Registry::get('uploadPrice');

					$this->_sendGAEvent();

					if($pp->user->balance <= (2*Zend_Registry::get('uploadPrice'))) $this->emails->balanceLow(array('user'=>$pp->user));
					$userModel->save($pp->user);
					if($pp->autorenew=='once'){
						$pp->autorenew='never';
						$productModel->save($pp);
					}
				}
			} else {
				CB_Resource_Functions::logEvent('productDeactivated', array('product'=>$pp));
				//CB_Resource_Functions::addFeed('productExpired', $pp->user->get(), $pp);
				$pp->status=3;
				$this->emails->productDeactivated(array('user'=>$pp->user, 'product'=>$pp));
				$productModel->save($pp, true);
			}
		}



		$hour=date('G');
		if($hour==3){
			$orderModel=new \CB\Model\Order();
			$ratingModel=new \CB\Model\Rating();
			$today=strtotime(date('Y-m-d'));
			$dayBefore20=strtotime('-19 days', $today);
			$dayBefore14=strtotime('-14 days', $today);
			$dayBefore7=strtotime('-7 days', $today);
			$orderModel->initQb();
			$orderModel->qb->field('date')->gt(new DateTime(date('Y-m-d', $dayBefore20)));
			$orders=$orderModel->runQuery();
			foreach($orders as $order){
				$orderTime=strtotime('today', $order->date->getTimestamp());
				if(empty($order->user_rating) && in_array($orderTime, array($dayBefore14, $dayBefore7))){
					$this->emails->ratingNotifyUser(array('product'=>$order->product->get(), 'user'=>$order->user->get(), 'shop_user'=>$order->shop_user->get(), 'order'=>$order));
				}
				if(empty($order->shop_user_rating) && in_array($orderTime, array($dayBefore14, $dayBefore7))){
					$this->emails->ratingNotifyShopUser(array('product'=>$order->product->get(), 'user'=>$order->user->get(), 'shop_user'=>$order->shop_user->get(), 'order'=>$order));
				}
				if($orderTime==$dayBefore20){
					if(empty($order->user_rating)){
						CB_Resource_Functions::logEvent('orderAutoRating', array('order'=>$order));
						$rating=new \CB\Rating();
						$rating->saveAll(array('date'=>new DateTime(), 'seller'=>false, 'success'=>false, 'positive'=>true, 'text'=>'Automatikus értékelés', 'product'=>$order->product));
						$ratingModel->save($rating);
						$order->user_rating=$rating;
						$orderModel->save($order);
					}
					if(empty($order->shop_user_rating)){
						CB_Resource_Functions::logEvent('orderAutoRating', array('order'=>$order));
						$rating=new \CB\Rating();
						$rating->saveAll(array('date'=>new DateTime(), 'seller'=>true, 'success'=>false, 'positive'=>true, 'text'=>'Automatikus értékelés', 'product'=>$order->product));
						$ratingModel->save($rating);
						$order->shop_user_rating=$rating;
						$orderModel->save($order);
					}
				}
			}
		}




		/**
		 * HÓNAP ELEJÉN 3-kor
		 */
		if(date('j')==1 && date('G')==3){
			$this->_imageClean();
		}

	}

	private function _sendGAEvent(){
		$tracker = new \GoogleAnalytics\Tracker('UA-48324090-1', 'csakbaba.hu');
		$visitor = new \GoogleAnalytics\Visitor();
		$visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
		$visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);
		$visitor->setScreenResolution('1024x768');
		$session = new GoogleAnalytics\Session();
		$tracker->trackEvent(new \GoogleAnalytics\Event('product', 'autorenew'), $session, $visitor);
	}

	public function scriptAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);



		/**
		 * SET SEARCH NAMES
		 */
		/*$productModel=new \CB\Model\Product();
		$products=$productModel->find();
		foreach($products as $product){
			$product->search_name=strtolower($product->name);
			$productModel->save($product, true);
		}*/

		/**
		 * IMAGICK WATERMARK TEST
		 */
		/*$img=APPLICATION_PATH.'/../public/img/elements/defaultproduct.png';
		$wm=APPLICATION_PATH.'/../public/img/elements/watermark.png';

		$image=new Imagick();
		$image->readimage($img);

		$watermark=new Imagick();
		$watermark->readimage($wm);
		$imageWidth=$image->getimagewidth();
		$imageHeight=$image->getimageheight();
		$wmratio=$watermark->getimagewidth()/$watermark->getimageheight();
		$watermark->scaleimage($imageWidth/4, ($imageWidth/4)/$wmratio);

		$image->compositeImage($watermark, imagick::COMPOSITE_OVER, $imageWidth*(3/4), ($imageHeight-($imageWidth/4)/$wmratio));

		header("Content-Type: image/" . $image->getImageFormat());
		echo $image;*/



		/**
		 * IMAGE CLEAN
		 */
		//$this->_imageClean();



		/**
		 * Image optimize
		 */
		/*$productImages=scandir(APPLICATION_PATH.'/../public/upload/product');
		$productImages=array_filter($productImages, function($item){
			return !in_array($item, array('.', '..')) && !is_dir(APPLICATION_PATH.'/../public/upload/product/'.$item);
		});
		foreach($productImages as $productImage){
			if(strpos($productImage, 'small.')!==false || strpos($productImage, 'mid.')!==false) continue;

			$image=new Zend_Image_Transform(APPLICATION_PATH.'/../public/upload/product/'.$productImage, new CB_Resource_Imagick());


			$newPath=APPLICATION_PATH.'/../public/upload/product/new/';
			list($fnroot, $ext) = $this->_fileExplode($productImage);
			$image->getDriver()->setJPG();

			$image->save($newPath.$fnroot.'.jpg');


			$image->fitIn(600, 600);
			$image->save($newPath.$fnroot.'_mid'.'.jpg');
			$image->fitIn(200, 200);
			$image->save($newPath.$fnroot.'_small'.'.jpg');

			exec('jpegoptim --max=75 '.$newPath.$fnroot.'_small'.'.jpg');
			exec('jpegoptim --max=75 '.$newPath.$fnroot.'_mid'.'.jpg');
			exec('jpegoptim --max=75 '.$newPath.$fnroot.'.jpg');
		}*/


		/*$userImages=scandir(APPLICATION_PATH.'/../public/upload/avatar');
		$userImages=array_filter($userImages, function($item){
			return !in_array($item, array('.', '..')) && !is_dir(APPLICATION_PATH.'/../public/upload/avatar/'.$item);
		});
		foreach($userImages as $userImage){
			if(strpos($userImage, 'small.')!==false || strpos($userImage, 'mid.')!==false) continue;

			$image=new Zend_Image_Transform(APPLICATION_PATH.'/../public/upload/avatar/'.$userImage, new CB_Resource_Imagick());


			$newPath=APPLICATION_PATH.'/../public/upload/avatar/new/';
			list($fnroot, $ext) = $this->_fileExplode($userImage);
			$image->getDriver()->setJPG();

			$image->save($newPath.$fnroot.'.jpg');


			$image->fitIn(600, 600);
			$image->save($newPath.$fnroot.'_mid'.'.jpg');
			$image->fitIn(200, 200);
			$image->save($newPath.$fnroot.'_small'.'.jpg');
		}*/




		/*$categories=Zend_Registry::get('categories');
		foreach($categories->_singleArray as $cat){
			if(!empty($cat->children)) continue;
			$path=$categories->getPath($cat->id);
			foreach(array_reverse($path) as $key=>$p){
				$path[$key]=$p->name;
			}
			//pr(implode(' > ', $path));
			pr($cat->id);
		}*/





		//$tracker->trackPageview($page, $session, $visitor);


		$userModel=new \CB\Model\User();
		$users=$userModel->find();
		foreach($users as $user){
			$name='';
			if(!empty($user->username)) $name=$user->username;
			if(!empty($user->address['name'])) $name=$user->address['name'];
			if(!empty($user->postaddress['name'])) $name=$user->postaddress['name'];

			echo $user->email."\t\"".$name."\"\n";
		}


		die();

	}


	private function _imageClean(){
		/**
		 * PRODUCT
		 */
		$productModel=new \CB\Model\Product();
		$products=$productModel->find();
		$usedFiles=array();
		foreach($products as $product){
			if(is_array($product->images)){
				foreach($product->images as $image){
					if(!empty($image['url'])) $usedFiles[]=end(explode('/', $image['url']));
					if(!empty($image['mid'])) $usedFiles[]=end(explode('/', $image['mid']));
					if(!empty($image['small'])) $usedFiles[]=end(explode('/', $image['small']));
				}
			}
		}
		$uploadedImages=scandir(APPLICATION_PATH.'/../public/upload/product');
		$diff=array_diff($uploadedImages, $usedFiles);
		$diff=array_filter($diff, function($item){
			return !in_array($item, array('.', '..')) && !is_dir(APPLICATION_PATH.'/../public/upload/avatar/'.$item);
		});
		foreach($diff as $d){
			unlink(APPLICATION_PATH.'/../public/upload/product/'.$d);
		}


		/**
		 * User
		 */
		$userModel=new \CB\Model\User();
		$users=$userModel->find();
		$usedFiles=array();
		foreach($users as $user){
			if(is_array($user->avatar)){
				foreach($user->avatar as $image){
					if(!empty($image['url'])) $usedFiles[]=end(explode('/', $image['url']));
					if(!empty($image['mid'])) $usedFiles[]=end(explode('/', $image['mid']));
					if(!empty($image['small'])) $usedFiles[]=end(explode('/', $image['small']));
				}
			}
		}
		$uploadedImages=scandir(APPLICATION_PATH.'/../public/upload/avatar');
		$diff=array_diff($uploadedImages, $usedFiles);
		$diff=array_filter($diff, function($item){
			return !in_array($item, array('.', '..')) && !is_dir(APPLICATION_PATH.'/../public/upload/avatar/'.$item);
		});
		foreach($diff as $d){
			unlink(APPLICATION_PATH.'/../public/upload/avatar/'.$d);
		}
	}


	private function _fileExplode($filename){
		$dotPosition=strrpos($filename, '.');
		$extension=substr($filename, $dotPosition+1);
		$filenameroot=substr($filename, 0, $dotPosition);
		return array($filenameroot, '.'.$extension);
	}




	public function phoneAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);
		$userModel=new \CB\Model\User();
		$users=$userModel->find();
		foreach($users as $user){
			/*$user->phone=str_replace(array('(',')',' ','-'), '', $user->phone);
			$userModel->save($user, true);*/
			pr($user->phone);
		}
	}


}