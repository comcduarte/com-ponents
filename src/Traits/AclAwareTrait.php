<?php
namespace Components\Traits;


trait AclAwareTrait
{
    protected $acl_service = NULL;
    
    public function setAclService($acl_service)
    {
        $this->acl_service = $acl_service;
        
        return $this;
    }
    
    public function getAclService()
    {
        return $this->acl_service;
    }
    
    public function isAllowed($roles = [], $resource, $privilege)
    {
        if (is_null($roles)) {
            return FALSE;
        }
        
        foreach ($roles as $role_object) {
            if ($this->getAclService()->isAllowed($role_object['ROLENAME'], $resource, $privilege)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}