<?php
	class DB{
		public static $connection;
		
		
		public function __construct(){
			if(!isset(self::$connection))
				self::$connection=new PDO('mysql:host=localhost;dbname=GiftShop;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
		}
		
		public function __destruct(){
			self::$connection=null;
		}
        
        public function readCategories(){

            $query="select * from category";

            $stmt=self::$connection->query($query);

			if (($result = $stmt->fetchAll(PDO::FETCH_OBJ)) != null) 
                //TODO: kreiraj objekte category
				return json_encode($result);
			 else
				return null;
		}
        
        public function readUser($id){
            
            $query = "select * from user where id = :idUser";
            
            $stmt = self::$connection->prepare($query);
            
            $stmt->bindParam(":idUser", $id, PDO::PARAM_STR);
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            if(count($result) == 1){
                return json_encode($result[0]);
            }else{
                return NULL;
            }
        }

        public function readUserLogin($email, $password){
            
            $query = "select * 
            from user 
            where email = :email and password = :password";
            
            $stmt = self::$connection->prepare($query);
            
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $password, PDO::PARAM_STR);
            
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if(count($result) == 1){
                return $result[0];
            }else {
                return NULL;
            }
        }
        
        public function readGift($id){
            
            $query = "select g.*, c.Description as Category 
            from gift g join category c on c.id = g.categoryId 
            where g.id=:id";
            
            $stmt = self::$connection->prepare($query);
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_OBJ)[0];
            
            return json_encode($result);
        }
        
        public function readGiftsForOwner($ownerId){
            
            $query = "select * 
            from gift 
            where ownerid=:ownerId and active = 1";
            
            $stmt = self::$connection->prepare($query);
            
            $stmt->bindParam(":ownerId", $ownerId, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return json_encode($result);
        }
        
        public function readOffersForOwner($ownerId) {
            
            $query = "select o.*, g.Description, g.Name 
            from offer o join gift g on g.id = o.giftId 
            where o.ownerid=:ownerId";
            
            $stmt = self::$connection->prepare($query);
            
            $stmt->bindParam(":ownerId", $ownerId, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return json_encode($result);
        }
        
        public function readOffersForGift($giftId){
            $query = "select o.*, u.fullName as offerer, u.id as offererId
                    from offer o join gift g on o.giftId = g.id join user u on u.id = o.offererId
                    where g.id =:giftId";

            $stmt = self::$connection->prepare($query);
            
            $stmt->bindParam(":giftId", $giftId, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return json_encode($result);
        }
        public function readGiftsBySearchTerm($ownerId, $searchTerm){
            
            $query = "select g.* 
                    from gift g join category c on c.id = g.categoryId 
                    where ownerId<>:id and (g.name like :searchTerm or g.description like :searchTerm or c.description like :searchTerm) ";
            
            $stmt = self::$connection->prepare($query);
            
            $term = "%".$searchTerm."%";
            
            $stmt->bindParam(":id", $ownerId, PDO::PARAM_INT);
            $stmt->bindParam(":searchTerm", $term, PDO::PARAM_STR);
            
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            return json_encode($result);
        }
        
        public function readGiftsForCategory($ownerId, $categoryId){
            
            if($categoryId != "-1"){
                $query = "select * from gift where ownerId<>:id and categoryId = :categoryId ";
            
                $stmt = self::$connection->prepare($query);
            
                $stmt->bindParam(":id", $ownerId, PDO::PARAM_INT);
                $stmt->bindParam(":categoryId", $categoryId, PDO::PARAM_STR);
            
                $stmt->execute();
            
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            }else{
                $query = "select * from gift where ownerId<>:id";
            
                $stmt = self::$connection->prepare($query);
            
                $stmt->bindParam(":id", $ownerId, PDO::PARAM_INT);
            
                $stmt->execute();
            
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            
            return json_encode($result);
        }
        
        public function createGift($gift){
            $stmt = self::$connection->prepare("insert into gift values (null, :name, :description, :image1Path, :image2Path, :image3Path, :active, :categoryId, :ownerId)");
            //$stmt->bindParam(':id', $gift->id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $gift->name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $gift->description, PDO::PARAM_STR);
            $stmt->bindParam(':image1Path', $gift->image1Path, PDO::PARAM_STR);
            $stmt->bindParam(':image2Path', $gift->image2Path, PDO::PARAM_STR);
            $stmt->bindParam(':image3Path', $gift->image3Path, PDO::PARAM_STR);
            $stmt->bindParam(':active', $gift->active, PDO::PARAM_INT);
            $stmt->bindParam(':categoryId', $gift->categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':ownerId', $gift->ownerId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 1){
                $gift->id = self::$connection->lastInsertId();
                return $gift;
            }
            else
                return NULL;
        }
        public function updateGiftSetImagePath($giftId, $imageNumber, $imagePath){
             
             $query = "update gift set image".$imageNumber."Path = :imagePath where id = :giftId";
             $stmt = self::$connection->prepare($query);

             $stmt->bindParam(':giftId', $giftId, PDO::PARAM_INT);
             $stmt->bindParam(':imagePath', $imagePath, PDO::PARAM_STR);
             $stmt->execute();
         
             if ($stmt->rowCount() == 1)
                 return $giftId;
             else
                 return NULL;
        }

        public function createUser($user){
            $stmt = self::$connection->prepare("insert into user values (null, :email, :password, :fullName, :address, :phone, DEFAULT, :userTypeId)");

            $stmt->bindParam(':email', $user->email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $user->password, PDO::PARAM_STR);
            $stmt->bindParam(':fullName', $user->fullName, PDO::PARAM_STR);
            $stmt->bindParam(':address', $user->address, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $user->phone, PDO::PARAM_STR);
            // $stmt->bindParam(':created', $user->created, PDO::PARAM_INT);
            $stmt->bindParam(':userTypeId', $user->userTypeId, PDO::PARAM_INT);
            $stmt->execute();

             if ($stmt->rowCount() == 1){
                $user->id = self::$connection->lastInsertId();
                return $user;
             }
            else
                return NULL;  
        }
        
        public function createOffer($offer){
            $stmt = self::$connection->prepare("insert into offer values (null, :ownerId, :offererId, :giftId, DEFAULT, DEFAULT, :comment)");
            $stmt->bindParam(':ownerId', $offer->ownerId, PDO::PARAM_INT);
            $stmt->bindParam(':offererId', $offer->offererId, PDO::PARAM_INT);
            $stmt->bindParam(':giftId', $offer->giftId, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $offer->comment, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1){
                $offer->id = self::$connection->lastInsertId();
                return $offer;
            }
            else
                return NULL;
        }
        
        public function updateOffer($offerId, $offer){
            $stmt = self::$connection->prepare("update offer 
            set  ownerId = :ownerId, offererId = :offererId, giftId = :giftId, accepted = :accepted 
            where id = :offerId");

            $stmt->bindParam(':ownerId', $offer->ownerId, PDO::PARAM_INT);
            $stmt->bindParam(':offererId', $offer->offererId, PDO::PARAM_INT);
            $stmt->bindParam(':giftId', $offer->giftId, PDO::PARAM_INT);
            $stmt->bindParam(':accepted', $offer->accepted, PDO::PARAM_INT);
            $stmt->bindParam(':offerId', $offerId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 1)
                return $offer;
            else
                return NULL;
        }
        
        public function updateUser($userId, $user){
            $stmt = self::$connection->prepare("update user set id = :id, email = :email, password = :password, fullName = :fullName, address = :address, phone = :phone, created = :created, userTypeId = :userTypeId where id = :userId");
            $stmt->bindParam(':id', $user->id, PDO::PARAM_INT);
            $stmt->bindParam(':email', $user->email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $user->password, PDO::PARAM_STR);
            $stmt->bindParam(':fullName', $user->fullName, PDO::PARAM_STR);
            $stmt->bindParam(':address', $user->address, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $user->phone, PDO::PARAM_STR);
            $stmt->bindParam(':created', $user->created, PDO::PARAM_STR);
            $stmt->bindParam(':userTypeId', $user->userTypeId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 1)
                return $user;
            else
                return NULL;
        }
        
        public function deleteGift($giftId){

            $stmt = self::$connection->prepare("update gift set active = 0 where id = :giftId");
            
            $stmt->bindParam(':giftId', $giftId, PDO::PARAM_INT);
            
            $stmt->execute();
            
			if ($stmt->rowCount() == 1)
                return $giftId;
            else
                return NULL;
		}
        
	}
?>