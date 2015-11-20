<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Data;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Xml
 */
class Xml {

	const SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<T3FlexForms>
    <data>
        <sheet index="options">
            <language index="lDEF">
                <field index="settings.input">
                    <value index="vDEF">0</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>';

	const EXPECTING_FLUX_PRUNING = '<T3FlexForms>
    <data>
        <sheet index="options">
            <language index="lDEF">
                <el index="section"></el>
                <field index="settings.input">
                    <value index="vDEF">0</value>
                </field>
                <field index=""></field>
            </language>
        </sheet>
        <sheet index="second"></sheet>
    </data>
</T3FlexForms>';

	const EXPECTING_FLUX_REMOVALS = '<T3FlexForms>
    <data>
        <sheet index="options">
            <language index="lDEF">
                <field index="settings.input">
                    <value index="vDEF">0</value>
                </field>
                <el index="section">
                    <field index="nested">
                        <value index="vDEF">test</value>
                    </field>
                    <field index=""></field>
                </el>
                <el>
                    <field index="nested">
                        <value index="vDEF">test</value>
                    </field>
                    <field index=""></field>
                </el>
                <el>
					<field index="id">
						<value index="vDEF">aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</value>
					</field>
					<field index=""></field>
				</el>
                <field index=""></field>
            </language>
        </sheet>
    </data>
</T3FlexForms>';

	const EXPECTING_FLUX_TRANSFORMATIONS = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<T3FlexForms>
    <data>
        <sheet index="options">
            <language index="lDEF">
                <field index="transform.unknown">
                    <value index="vDEF">0</value>
                </field>
                <field index="transform.stringToArray">
                    <value index="vDEF">1,2,3</value>
                </field>
                <field index="transform.stringToInteger">
                    <value index="vDEF">3</value>
                </field>
                <field index="transform.stringToFloat">
                    <value index="vDEF">1.5</value>
                </field>
                <field index="transform.nullToInvalidClassName">
                    <value index="vDEF"></value>
                </field>
                <field index="transform.nullToGenericObject">
                    <value index="vDEF"></value>
                </field>
                <field index="transform.nullToGenericObjectCollection">
                    <value index="vDEF"></value>
                </field>
                <field index="transform.nullToDomainObject">
                    <value index="vDEF"></value>
                </field>
                <field index="transform.nullToDomainObjectCollection">
                    <value index="vDEF">0,1,2</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>';

}
