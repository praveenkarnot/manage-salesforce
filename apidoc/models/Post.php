<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * @OA\Info(title="Authority Rest APIs", version="1.0")
 */
class Post {

    /**
     * @OA\Post(
     *  path="/api/custom/external_authority/login",
     *  operationId="SignIn",
     *  tags={"Authority User"},
     *  summary="Authority User Login",
     *  description="Authority User login",
     *  @OA\RequestBody(
     *      required=true,
     *      description="User login with email, pass, authority_login_access_key and authority_login_secret_key",
     *      @OA\JsonContent(
     *          required={"email","pass","authority_login_access_key","authority_login_secret_key"},
     *          @OA\Property(property="email", type="string", example="abc@test.com"),
     *          @OA\Property(property="pass", type="string", example="password"),
     *          @OA\Property(property="authority_login_access_key", type="string", example="345retrdret546456#$rr454"),
     *          @OA\Property(property="authority_login_secret_key", type="string", example="Dert$%jyhhGHG@sdde56#Ws@"),
     *      )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
     *          @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Here is authority details"
    *                 ),
    *            @OA\Property(
    *                     property="logged_in_user_id",
    *                     type="integer",
    *                     example=34543
    *                 ),
    *             @OA\Property(
    *                  property="token_type",
    *                     type="string",
    *                     example="Bearer"
    *                 ),
    *                 @OA\Property(
    *                     property="organization_id",
    *                     type="integer",
    *                     example=646456
    *                 ),
    *               @OA\Property(
    *                     property="expires_in",
    *                     type="integer",
    *                     example=3000
    *                 ),
    *                 @OA\Property(
    *                     property="access_token",
    *                     type="string",
    *                     example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsI"
    *                 ),
    *                  @OA\Property(
    *                     property="refresh_token",
    *                     type="string",
    *                     example="def50200afac57531495013998ef6f187561214"
    *                 ),
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=400,
    *         description="Bad Request",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="400"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="The user credentials were incorrect"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *
    *             
    *         )
    *     ),
    * ),
    *    
    * 
    *  )
    */
    public function login(Request $request)
    {

    }

    /**
     * @OA\Post(
     * path="/api/custom/external_authority/refresh_token",
     * operationId="Refresh-token",
     * tags={"Authority User"},
     *  summary="Refresh token",
     *  description="Refresh token",
     *  @OA\RequestBody(
     *      required=true,
     *      description="",
     *      @OA\JsonContent(
     *          required={"refresh_token"},
     *          @OA\Property(property="refresh_token", type="string", example="ertyreggrtretertretertertert3$%454gdfg456546456"),
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *            @OA\Property(
    *                     property="logged_in_user_id",
    *                     type="integer",
    *                     example=34543
    *                 ),
    *             @OA\Property(
    *                  property="token_type",
    *                     type="string",
    *                     example="Bearer"
    *                 ),
    *                 @OA\Property(
    *                     property="organization_id",
    *                     type="integer",
    *                     example=646456
    *                 ),
    *               @OA\Property(
    *                     property="expires_in",
    *                     type="integer",
    *                     example=3000
    *                 ),
    *                 @OA\Property(
    *                     property="access_token",
    *                     type="string",
    *                     example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsI"
    *                 ),
    *                  @OA\Property(
    *                     property="refresh_token",
    *                     type="string",
    *                     example="def50200afac57531495013998ef6f187561214"
    *                 ),
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=401,
    *         description="Bad Request",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="401"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="The refresh token is invalid"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function refreshToken() {
        
    }

    /**
     * @OA\Post(
     * path="/api/custom/external_authority/search_address",
     * operationId="search-address",
     * tags={"Authority User"},
     *  summary="Search address",
     *  description="Search address",
     *   @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Set access_token value with Bearer in header. For example Bearer access_token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="This API need to call 2 times for get proper address/postcode. In 'input' parameter, you can pass postocde/address and In 'step' parameter, First you need to pass 1 and 'id' parameter will be null. After hit API, you will get 'id' into resposne. Now in 'step' parameter you need to pass 2 and 'id' which you received into step-1. In this step, you will get 'address_data' array and in this array you will get 'addressline1', 'addressline2', 'posttown' and 'postcode'",
     *      @OA\JsonContent(
     *          required={"logged_in_user_id", "organization_id","input", "step","id"},
     *          @OA\Property(property="logged_in_user_id", type="integer", example="34553"),
     *          @OA\Property(property="organization_id", type="integer", example="56756765"),
     *          @OA\Property(property="input", type="string", example="SL6 1RY"),
     *          @OA\Property(property="step", type="integer", example="1"),
     *          @OA\Property(property="id", type="string", example="UK@STATE|Berkshire@CTY|{Cookham@LOC,Maidenhead@PTN,SL@PCDA"),
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *            @OA\Property(
    *                     property="id",
    *                     type="object",
    *                        @OA\Property(
    *                          property="num_of_ids",
    *                           type="integer",
    *                         example=30
    *                         ),
    *                        @OA\Property(
    *                          property="id",
    *                           type="object",
    *                                  @OA\Property(
    *                                   property="id",
    *                                   type="string",
    *                                    example="UK@STATE|Berkshire@CTY|{Cookham@LOC,Maidenhead@PTN"
    *                             ),
    *                               
    *                               @OA\Property(
    *                                   property="summaryline",
    *                                   type="string",
    *                                    example="Cookham, SL6 9"
    *                             ),  
    *                              @OA\Property(
    *                                   property="locationsummary",
    *                                   type="string",
    *                                    example="Maidenhead, Berkshire"
    *                             ),   
    *                                  
    *                         ),
    *                 ),
    *             @OA\Property(
    *                  property="message",
    *                     type="string",
    *                     example="Data success"
    *                 ),
    *                
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function searchAddress() {
        
    }


    /**
     * @OA\Post(
     * path="/api/custom/external_authority/payment_companies",
     * operationId="payment-companies",
     * tags={"Authority User"},
     *  summary="Payment companies",
     *  description="Payment companies",
     *   @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Set access_token value with Bearer in header. For example Bearer access_token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="Payment company ID will need to pass into Create Client API, if client is attached with any payment company",
     *      @OA\JsonContent(
     *          required={"logged_in_user_id", "organization_id"},
     *          @OA\Property(property="logged_in_user_id", type="integer", example="34553"),
     *          @OA\Property(property="organization_id", type="integer", example="56756765"),
     *         
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *            @OA\Property(
    *                     property="payment_company_list",
    *                     type="object",
    *                        @OA\Property(
    *                          property="id",
    *                           type="integer",
    *                         example=12535
    *                         ),
    *                          @OA\Property(
    *                          property="name",
    *                           type="string",
    *                         example="MSN"
    *                         ),  
    *                       
    *                 ),
    *        
    *                
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function paymentCompanies() {
    }


    /**
     * @OA\Post(
     * path="/api/custom/external_authority/create_client",
     * operationId="create-client",
     * tags={"Authority User"},
     *  summary="Create client",
     *  description="Create client",
     *   @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Set access_token value with Bearer in header. For example Bearer access_token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="Create client API will need some parameters from Search address and payment company list. Regarding 'client_is_active_or_draft' this parameter, need to pass 1 for Active client OR 2 for Draft client. By-default value will be 1.",
     *      @OA\JsonContent(
     *          required={"logged_in_user_id", "organization_id", "client_is_active_or_draft", "email", "first_name", "last_name", "postcode", "street_address", "city", "mobile"},
     *          @OA\Property(property="logged_in_user_id", type="integer", example="34553"),
     *          @OA\Property(property="organization_id", type="integer", example="56756765"),
     *         @OA\Property(property="client_is_active_or_draft", type="integer", example="1 OR 2"),
     *  @OA\Property(property="email", type="string", example="agreement.test@yopmail.com"),
     *  @OA\Property(property="first_name", type="string", example="Mike"),
     *  @OA\Property(property="last_name", type="string", example="Joseph"),
     *  @OA\Property(property="postcode", type="string", example="SL6 1RY"),
     *  @OA\Property(property="street_address", type="string", example="43 Westwood Green OR pass blank"),
     *  @OA\Property(property="city", type="string", example="Maidenhead  OR pass blank"),
     *  @OA\Property(property="mobile", type="integer", example="7345453245"),
     *  @OA\Property(property="payment_company", type="integer", example="1232 OR 0"),
     *  @OA\Property(property="care_recipient_postcode", type="string", example="SL6 1ry OR pass blank value"),
     *  @OA\Property(property="street_address_2", type="string", example="Cookham OR pass blank value"),
     *         
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *          @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Client account created successfully"
    *                 ),
    *          @OA\Property(
    *                     property="client_id",
    *                     type="integer",
    *                     example="111097"
    *                 ),
    *        
    *                
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function createClient() {
    }


    /**
     * @OA\Post(
     * path="/api/custom/external_authority/logout",
     * operationId="Authority-logout",
     * tags={"Authority User"},
     *  summary="Authority logout",
     *  description="Authority logout",
     *   @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Set access_token value with Bearer in header. For example Bearer access_token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="",
     *      @OA\JsonContent(
     *          required={"logged_in_user_id"},
     *          @OA\Property(property="logged_in_user_id", type="integer", example="34553"),
     *         
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *            @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="User logout successfully"
    *                 ),
    *        
    *                
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function logout() {
    }


    /**
     * @OA\Post(
     * path="/api/custom/external_authority/add_payment_company",
     * operationId="add-payment-company",
     * tags={"Authority User"},
     *  summary="Add Payment company",
     *  description="Manage Payment company",
     *   @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Set access_token value with Bearer in header. For example Bearer access_token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="Parameter: company_payment_terms, it is optional parameter and can pass these paramters. 0: NA,1:'1 week in arrears', 2: '2 weeks in arrears', 3:'3 weeks in arrears',4:'4 weeks in arrears' ",
     *      @OA\JsonContent(
     *          required={"logged_in_user_id", "organization_id","company_name", "company_contact_full_name", "company_mail", "company_mobile"},
     *          @OA\Property(property="logged_in_user_id", type="integer", example="34553"),
     *          @OA\Property(property="organization_id", type="integer", example="56756765"),
     *          @OA\Property(property="company_name", type="string", example="Amazon"),
     *          @OA\Property(property="company_contact_full_name", type="string", example="David John"),
     *          @OA\Property(property="company_mail", type="string", example="david@yopmail.com"),
     *          @OA\Property(property="company_mobile", type="int", example="7345345432"),
     *          @OA\Property(property="company_payment_terms", type="int", example="0 OR 1 OR 2 OR 3 OR 4"),
     *         
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *            @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Payment company is created successfully"
    *                 ),
    *            @OA\Property(
    *                     property="payament_company_id",
    *                     type="integer",
    *                     example=464563
    *                 ),
    *        
    *                
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function addPaymentCompany() {
    }

    /**
     * @OA\Post(
     * path="/api/custom/external_authority/common_data_config",
     * operationId="common-data-config-list",
     * tags={"Authority User"},
     *  summary="Common data configuration list",
     *  description="This Common data configuration list API paramters will be use into Post Job API. You just need to pass keys of this API into Post Job API",
     *   @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Set access_token value with Bearer in header. For example Bearer access_token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *  @OA\RequestBody(
     *      required=true,
     *      description=" ",
     *      @OA\JsonContent(
     *          required={"logged_in_user_id", "organization_id"},
     *          @OA\Property(property="logged_in_user_id", type="integer", example="34553"),
     *          @OA\Property(property="organization_id", type="integer", example="56756765"),
     *         
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *                 @OA\Property(
    *                     property="what_type_of_care_do_you_require",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="object",
    *                           @OA\Property(
    *                             property="Hourly care,Hourly",
    *                             type="string",
    *                             example="Hourly care,Hourly"
    *                            ),
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="object",
    *                             @OA\Property(
    *                              property="Overnight care, Sleeping",
    *                              type="string",
    *                              example="Overnight care, Sleeping"
    *                        ),
    *                    ),       

    *        
    *                ),

    *           ),

    *                 @OA\Property(
    *                     property="languages",
    *                     type="object",
    *                        @OA\Property(
    *                          property="Punjabi",
    *                           type="string",
    *                              example="Punjabi"
    *                    ),
    *                      @OA\Property(
    *                          property="Romanian",
    *                           type="string",
    *                              example="Romanian"
    *                    ),
    *                      
    *                ),

     *                 @OA\Property(
    *                     property="carer_interest",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Art"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="Music"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="what_experience_should_you_have",
    *                     type="object",
    *                        @OA\Property(
    *                          property="Early Stage Dementia",
    *                           type="string",
    *                              example="Early Stage Dementia"
    *                    ),
    *                      @OA\Property(
    *                          property="Late Stage Dementia",
    *                           type="string",
    *                              example="Late Stage Dementia"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="what_support_do_you_need_help_with",
    *                     type="object",
    *                        @OA\Property(
    *                          property="help_companionship",
    *                           type="string",
    *                              example="'Companionship and household tasks', 'Companionship, cooking, housekeeping, medical prompting'"
    *                    ),
    *                      @OA\Property(
    *                          property="help_medication_prompting",
    *                           type="string",
    *                              example="'Specialist experience', 'Early stage dementia, physical disabilities, late stage dementia'"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="week_days",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Monday"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="Tuesday"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="care_duration_type",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Ongoing"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="Fixed"
    *                    ),
    *                      
    *                ),

     *                 @OA\Property(
    *                     property="when_care_start",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="As soon as possible"
    *                    ),
    *                      @OA\Property(
    *                          property="3",
    *                           type="string",
    *                              example="Select a start date"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="who_is_care_for",
    *                     type="object",
    *                        @OA\Property(
    *                          property="myself",
    *                           type="string",
    *                              example="My Self"
    *                    ),
    *                      @OA\Property(
    *                          property="family",
    *                           type="string",
    *                              example="Family"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="gender",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Male"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="Female"
    *                    ),
    *                     @OA\Property(
    *                          property="3",
    *                           type="string",
    *                              example="No preference"
    *                    ),    
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="has_driving_license",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Yes"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="No"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="pet_friendly",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Yes"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="No"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="non_smoker",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Yes"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="No"
    *                    ),
    *                      
    *                ),

    *                 @OA\Property(
    *                     property="works_with_children",
    *                     type="object",
    *                        @OA\Property(
    *                          property="1",
    *                           type="string",
    *                              example="Yes"
    *                    ),
    *                      @OA\Property(
    *                          property="2",
    *                           type="string",
    *                              example="No"
    *                    ),
    *                      
    *                ),


    *           ),
    
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function commonDataConfiguration() {
    }


    /**
     * @OA\Post(
     * path="/api/custom/external_authority/post_job",
     * operationId="post-job",
     * tags={"Authority User"},
     *  summary="Post job",
     *  description="Post job",
     *   @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Set access_token value with Bearer in header. For example Bearer access_token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *  @OA\RequestBody(
     *      required=true,
     *      description="Post job API will need some parameters from 'Common data configuration list' API. ",
     *      @OA\JsonContent(
     *          required={"logged_in_user_id", "organization_id", "client_id", "first_name_of_care_recipient", "last_name_of_care_recipient", "age_of_care_recipient", "what_type_of_care_do_you_require", "care_duration_type", "when_care_start", "care_start_date", "what_experience_should_you_have", "what_support_do_you_need_help_with", "gender", "week_days", "has_driving_license", "who_is_care_for","languages","carer_interest"},
     * 
     * @OA\Property(property="logged_in_user_id", type="integer", example="34553"),
     * @OA\Property(property="organization_id", type="integer", example="56756765"),
     * @OA\Property(property="client_id", type="integer", example="234343"),
     *  @OA\Property(property="age_of_care_recipient", type="int", example="47"),
     *  @OA\Property(property="first_name_of_care_recipient", type="string", example="Mike"),
     *  @OA\Property(property="last_name_of_care_recipient", type="string", example="Joseph"),
     *  @OA\Property(property="what_type_of_care_do_you_require", type="int", example="2"),
     *  @OA\Property(property="care_duration_type", type="int", example="1 OR 2"),
     *  @OA\Property(property="when_care_start", type="int", example="1 OR 3"),
     *  @OA\Property(property="care_start_date", type="integer", example="1683133956"),
     *  @OA\Property(property="gender", type="int", example="1 Or 2"),
     *  @OA\Property(property="week_days", type="int", example="1,2"),
     *  @OA\Property(property="number_of_hours_per_day", type="int", example=2),
     *  @OA\Property(property="has_driving_license", type="int", example="1,2"),
    *  @OA\Property(property="who_is_care_for", type="string", example="myself"),
    *  @OA\Property(property="carer_interest", type="int", example="['1','9']"),
    *  @OA\Property(property="languages", type="string", example="['Punjabi','Gujarati','German']"),
    *  @OA\Property(property="what_experience_should_you_have", type="string", example="['Early Stage Dementia','COPD all capital letters']"),
     *  @OA\Property(property="what_support_do_you_need_help_with", type="string", example="['help_dressingundressing','help_medication_prompting','help_companionship','help_hoisting']"),
    
     *         
     *       )
     *  ),
     *  @OA\Response(
     *       response=200,
     *       description="Response",
     *       @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer",example="200"),
     *          @OA\Property(property="status", type="boolean",format="true|false"),
     *          @OA\Property(property="data", type="object",
    *          @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Job posted successfully"
    *                 ),
    *         
    *        
    *                
    *           ),
    *          
    *       )
    *  )
    * ,  
    *    @OA\Response(
    *         response=403,
    *         description="Bad Request (Format will be same, only message will be vary as per the error occur.)",
    *         @OA\JsonContent(
    *            @OA\Property(property="status_code", type="integer",example="403"),
    *            @OA\Property(property="status", type="boolean",format="false", example="FALSE"),
     *           @OA\Property(property="data", type="object",
     *           @OA\Property(
    *                     property="message",
    *                     type="string",
    *                     example="Required parameters are missing"
    *                 ),
    *             
    *         )
    *     ),
    * ),
    *   
    *  )
    */
    public function postJob() {
    }




}

