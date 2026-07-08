-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema DormitoryDB
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema DormitoryDB
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `DormitoryDB` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `DormitoryDB` ;

-- -----------------------------------------------------
-- Table `DormitoryDB`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DormitoryDB`.`user` (
  `user_name` VARCHAR(45) NOT NULL COMMENT '사용자 이름 (PK 1)',
  `student_id` INT NOT NULL COMMENT '학번 (PK 2)',
  `password` VARCHAR(45) NOT NULL,
  `phone_num` INT NOT NULL,
  `address` VARCHAR(45) NOT NULL,
  `Ingredient_ing_id` INT NOT NULL,
  PRIMARY KEY (`student_id`),
  INDEX `fk_user_Ingredient1_idx` (`Ingredient_ing_id` ASC) VISIBLE,
  CONSTRAINT `fk_user_Ingredient1`
    FOREIGN KEY (`Ingredient_ing_id`)
    REFERENCES `DormitoryDB`.`Ingredient` (`ing_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `DormitoryDB`.`Ingredient`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DormitoryDB`.`Ingredient` (
  `ing_id` INT NOT NULL COMMENT '재료 고유 ID (PK로 가정)',
  `ing_name` VARCHAR(45) NOT NULL,
  `MFG_DATETIME` DATE NOT NULL,
  `storage_loc` VARCHAR(45) NOT NULL,
  `EXP_DATETIME` DATE NOT NULL,
  `state` VARCHAR(45) NOT NULL,
  `user_student_id` INT NOT NULL,
  PRIMARY KEY (`ing_id`),
  INDEX `fk_Ingredient_user1_idx` (`user_student_id` ASC) VISIBLE,
  CONSTRAINT `fk_Ingredient_user1`
    FOREIGN KEY (`user_student_id`)
    REFERENCES `DormitoryDB`.`user` (`student_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `DormitoryDB`.`Refrigerator`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DormitoryDB`.`Refrigerator` (
  `ref_id` INT NOT NULL COMMENT '냉장고 ID (PK)',
  `Zone` VARCHAR(45) NOT NULL,
  `Ingredient_ing_id` INT NOT NULL,
  `user_student_id` INT NOT NULL,
  PRIMARY KEY (`ref_id`),
  INDEX `fk_Refrigerator_Ingredient1_idx` (`Ingredient_ing_id` ASC) VISIBLE,
  INDEX `fk_Refrigerator_user1_idx` (`user_student_id` ASC) VISIBLE,
  CONSTRAINT `fk_Refrigerator_Ingredient1`
    FOREIGN KEY (`Ingredient_ing_id`)
    REFERENCES `DormitoryDB`.`Ingredient` (`ing_id`),
  CONSTRAINT `fk_Refrigerator_user1`
    FOREIGN KEY (`user_student_id`)
    REFERENCES `DormitoryDB`.`user` (`student_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `DormitoryDB`.`advisor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DormitoryDB`.`advisor` (
  `advisor_id` INT NOT NULL COMMENT '관리자/조언자 ID (PK)',
  `advisor_name` VARCHAR(45) NOT NULL,
  `advisor_password` VARCHAR(45) NOT NULL,
  `Ingredient_ing_id` INT NOT NULL,
  `user_student_id` INT NOT NULL,
  PRIMARY KEY (`advisor_id`),
  INDEX `fk_advisor_Ingredient1_idx` (`Ingredient_ing_id` ASC) VISIBLE,
  INDEX `fk_advisor_user1_idx` (`user_student_id` ASC) VISIBLE,
  CONSTRAINT `fk_advisor_Ingredient1`
    FOREIGN KEY (`Ingredient_ing_id`)
    REFERENCES `DormitoryDB`.`Ingredient` (`ing_id`),
  CONSTRAINT `fk_advisor_user1`
    FOREIGN KEY (`user_student_id`)
    REFERENCES `DormitoryDB`.`user` (`student_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `DormitoryDB`.`advisor_has_Refrigerator`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DormitoryDB`.`advisor_has_Refrigerator` (
  `advisor_advisor_id` INT NOT NULL COMMENT 'advisor 참조 (FK)',
  `Refrigerator_ref_id` INT NOT NULL COMMENT 'Refrigerator 참조 (FK)',
  PRIMARY KEY (`advisor_advisor_id`, `Refrigerator_ref_id`),
  INDEX `fk_advisor_has_Refrigerator_Refrigerator1_idx` (`Refrigerator_ref_id` ASC) VISIBLE,
  INDEX `fk_advisor_has_Refrigerator_advisor1_idx` (`advisor_advisor_id` ASC) VISIBLE,
  CONSTRAINT `fk_advisor_has_Refrigerator_advisor1`
    FOREIGN KEY (`advisor_advisor_id`)
    REFERENCES `DormitoryDB`.`advisor` (`advisor_id`),
  CONSTRAINT `fk_advisor_has_Refrigerator_Refrigerator1`
    FOREIGN KEY (`Refrigerator_ref_id`)
    REFERENCES `DormitoryDB`.`Refrigerator` (`ref_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
