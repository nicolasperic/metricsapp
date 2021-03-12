<?php
/**
 * The only responsibility of this class is to generate an Entity from a DTO
 */
namespace App\Dto\Mapper;

use App\AssemblaUser;
use App\Dto\AssemblaUserDto;


class AssemblaUserMapper extends AbstractMapper
{
    public static function createAssemblaUserFromDTO(AssemblaUserDto $assemblaUserDto)
    {
        return AssemblaUser::create([
            'user_assembla_id' => $assemblaUserDto->getUserAssemblaId(),
            'login' => $assemblaUserDto->getLogin(),
            'name' => $assemblaUserDto->getName(),
            'email' => $assemblaUserDto->getEmail(),
            'picture' => $assemblaUserDto->getPicture()
        ]);
    }
    //TODO create updateAssemblaUserFromDTO function to be able to sync users after creation... hmm this is not important, validate if users are removed from spaces correctly

}