-- MySQL Workbench Forward Engineering

-- ---------------------------------------------------------------------
-- [1] 초기 설정 및 외래 키 검사 비활성화 (기존 데이터 삭제 및 재생성 위해)
-- ---------------------------------------------------------------------
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
-- 학생 정보 테이블 (학번은 자동증가가 아닌 고유값으로 유지)
CREATE TABLE IF NOT EXISTS `user` (
  `user_name` VARCHAR(45) NOT NULL COMMENT '사용자 이름',
  `student_id` INT NOT NULL COMMENT '학번 (PK)',
  `password` VARCHAR(45) NOT NULL,
  `phone_num` VARCHAR(20) NOT NULL COMMENT '010 등으로 시작하므로 VARCHAR 권장',
  `address` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`student_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `advisor`
-- -----------------------------------------------------
-- 관리자 테이블
CREATE TABLE IF NOT EXISTS `advisor` (
  `advisor_id` INT NOT NULL AUTO_INCREMENT COMMENT '관리자 ID (PK, 자동증가)',
  `advisor_name` VARCHAR(45) NOT NULL,
  `advisor_password` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`advisor_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `Fridge` (수정됨: zone 컬럼 삭제)
-- -----------------------------------------------------
-- fridge_new.php 및 fridge_manage.php와 연동
CREATE TABLE IF NOT EXISTS `Fridge` (
  `fridge_id` INT NOT NULL AUTO_INCREMENT COMMENT '냉장고 ID (PK, 자동증가)',
  `fridge_name` VARCHAR(45) NOT NULL COMMENT '냉장고 이름',
  PRIMARY KEY (`fridge_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `Ingredient`
-- -----------------------------------------------------
-- add_ingredient.php와 연동 (fridge_id 추가됨)
CREATE TABLE IF NOT EXISTS `Ingredient` (
  `ing_id` INT NOT NULL AUTO_INCREMENT COMMENT '재료 고유 ID (PK, 자동증가)',
  `ing_name` VARCHAR(45) NOT NULL,
  `MFG_DATETIME` DATE NOT NULL COMMENT '제조일',
  `storage_loc` VARCHAR(45) NOT NULL COMMENT '상세 위치 (몇 번째 칸 등)',
  `EXP_DATETIME` DATE NOT NULL COMMENT '유통기한',
  `state` VARCHAR(45) NOT NULL DEFAULT '정상' COMMENT '상태 (정상/폐기대상)',
  `user_student_id` INT NOT NULL COMMENT '등록한 학생 ID (FK)',
  `fridge_id` INT NOT NULL COMMENT '보관된 냉장고 ID (FK)',
  PRIMARY KEY (`ing_id`),
  
  -- 외래 키: 학생 연결
  INDEX `fk_Ingredient_user1_idx` (`user_student_id` ASC),
  CONSTRAINT `fk_Ingredient_user1`
    FOREIGN KEY (`user_student_id`)
    REFERENCES `user` (`student_id`)
    ON DELETE CASCADE,

  -- 외래 키: 냉장고 연결 
  INDEX `fk_Ingredient_Fridge1_idx` (`fridge_id` ASC),
  CONSTRAINT `fk_Ingredient_Fridge1`
    FOREIGN KEY (`fridge_id`)
    REFERENCES `Fridge` (`fridge_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `DisposeHistory` (신규 추가)
-- -----------------------------------------------------
-- dispose.php와 연동 (폐기 이력 저장)
CREATE TABLE IF NOT EXISTS `DisposeHistory` (
  `dispose_id` INT NOT NULL AUTO_INCREMENT COMMENT '폐기 기록 ID (PK)',
  `ing_id` INT NOT NULL COMMENT '폐기된 재료 ID (FK)',
  `advisor_id` INT NOT NULL COMMENT '처리한 관리자 ID (FK)',
  `dispose_date` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '폐기 처리 일시',
  PRIMARY KEY (`dispose_id`),
  
  INDEX `fk_Dispose_Ingredient_idx` (`ing_id` ASC),
  INDEX `fk_Dispose_Advisor_idx` (`advisor_id` ASC),
  
  CONSTRAINT `fk_Dispose_Ingredient`
    FOREIGN KEY (`ing_id`)
    REFERENCES `Ingredient` (`ing_id`)
    ON DELETE CASCADE,
    
  CONSTRAINT `fk_Dispose_Advisor`
    FOREIGN KEY (`advisor_id`)
    REFERENCES `advisor` (`advisor_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- [3] 마무리 및 외래 키 검사 활성화
-- ---------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET FOREIGN_KEY_CHECKS = 1;