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
        return $assemblaUser = AssemblaUser::create([
            'user_assembla_id' => $assemblaUserDto->getUserAssemblaId(),
            'login' => $assemblaUserDto->getLogin(),
            'name' => $assemblaUserDto->getName(),
            'email' => $assemblaUserDto->getEmail(),
            'picture' => $assemblaUserDto->getPicture()
        ]);


    }

}