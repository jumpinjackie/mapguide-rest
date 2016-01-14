<?php

//
//  Copyright (C) 2016 by Jackie Ng
//
//  This library is free software; you can redistribute it and/or
//  modify it under the terms of version 2.1 of the GNU Lesser
//  General Public License as published by the Free Software Foundation.
//
//  This library is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//  Lesser General Public License for more details.
//
//  You should have received a copy of the GNU Lesser General Public
//  License along with this library; if not, write to the Free Software
//  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
//

/**
 * @SWG\Swagger(
 *     schemes={"http","https"},
 *     basePath="/mapguide/rest",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="mapguide-rest",
 *         description="<p>mapguide-rest provides a REST API for MapGuide Open Source and Autodesk Infrastructure Map Server</p><p><strong>NOTE:</strong> Basic HTTP authentication credentials will generally be cached by the web browser for a short period should you choose to use this method instead of passing in session ids</p>",
 *         @SWG\License(
 *             name="LGPL 2.1",
 *             url="http://www.gnu.org/licenses/lgpl-2.1.txt"
 *         )
 *     ),
 *     @SWG\ExternalDocumentation(
 *         description="mapguide-rest on GitHub",
 *         url="https://github.com/jumpinjackie/mapguide-rest"
 *     )
 * )
 */
 
/**
 * @SWG\Tag(name="coordsys", description="Coordinate System Catalog")
 * @SWG\Tag(name="data", description="Data Publishing Framework")
 * @SWG\Tag(name="library", description="Site Repository")
 * @SWG\Tag(name="providers", description="FDO Provider Registry")
 * @SWG\Tag(name="services", description="Additional Services")
 * @SWG\Tag(name="session", description="Session Repository")
 * @SWG\Tag(name="site", description="Site Service")
 */

?>