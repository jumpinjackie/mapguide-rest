<?php
namespace Swagger\Contexts;

/**
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 *             Copyright [2013] [Robert Allen]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @category   Swagger
 * @package    Swagger
 */

/**
 * Context
 *
 * @category   Swagger
 * @package    Swagger
 */
class ClassContext extends Context
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $extends;

    /**
     * @param string $class      class
     * @param string $extends    extends
     * @param string $docComment docComment
     */
    public function __construct($class, $extends, $docComment)
    {
        parent::__construct($docComment);
        $this->class = $class;
        $this->extends = $extends;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getExtends()
    {
        return $this->extends;
    }
}
