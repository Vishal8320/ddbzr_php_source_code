<?php

                    require_once(__DIR__.'/config.php');
                    require_once(__DIR__.'/classes.php');
                  
                     $db = new mysqli($CONF['host'],$CONF['user'],$CONF['pass'],$CONF['name']);
                     if($db->connect_errno){
                         echo "failed to connect Mysql (".$db->connect_errno.") ". $db->connect_error;  
                     }
                     $db->set_charset("utf8mb4");
                    

                    $resultSettings = $db->query(getsettings());
                    if($resultSettings){
                        $settings = $resultSettings->fetch_assoc();
                    }else{
                        echo "Error: ".$db->error;
                    }

                    //  Class DB_CON{
                    //      public $con;
                    //   public function create_con(){
                    //     global $CONF;
                    //    // $this->con = $con;
                    //     $this->con = mysqli_connect($CONF['host'],$CONF['user'],$CONF['pass'],$CONF['name']);
                              
                    //           if(mysqli_connect_errno()){
                    //               echo "failed to connect MYSQL: " . mysqli_connect_errno();

                    //           }
                    //         }
                    //         public function __destruct()
                    //         {
                                
                    //         if(mysqli_close($this->con)){
                    //             echo 'Database Connection Closed';
                    //         }
                                
                    //         }



                    //  }



?>