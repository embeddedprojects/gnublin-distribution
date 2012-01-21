/***************************************************************************/
/*                                                                         */
/*  fterrdef.h                                                             */
/*                                                                         */
/*    FreeType error codes (specification).                                */
/*                                                                         */
/*  Copyright 2002, 2004, 2006, 2007, 2010 by                              */
/*  David Turner, Robert Wilhelm, and Werner Lemberg.                      */
/*                                                                         */
/*  This file is part of the FreeType project, and may only be used,       */
/*  modified, and distributed under the terms of the FreeType project      */
/*  license, LICENSE.TXT.  By continuing to use, modify, or distribute     */
/*  this file you indicate that you have read the license and              */
/*  understand and accept it fully.                                        */
/*                                                                         */
/***************************************************************************/


  /*******************************************************************/
  /*******************************************************************/
  /*****                                                         *****/
  /*****                LIST OF ERROR CODES/MESSAGES             *****/
  /*****                                                         *****/
  /*******************************************************************/
  /*******************************************************************/


  /* You need to define both FT_ERRORDEF_ and FT_NOERRORDEF_ before */
  /* including this file.                                           */


  /* generic errors */

  FT_NOERRORDEF_( Ok,                                        0x00, \
                  "no error" )

  FT_ERRORDEF_( Cannot_Open_Resource,                        0x01, \
                "cannot open resource" )
  FT_ERRORDEF_( Unknown_File_Format,                         0x02, \
                "unknown file format" )
  FT_ERRORDEF_( Invalid_File_Format,                         0x03, \
                "broken file" )
  FT_ERRORDEF_( Invalid_Version,                             0x04, \
                "invalid FreeType version" )
  FT_ERRORDEF_( Lower_Module_Version,                        0x05, \
                "module version is too low" )
  FT_ERRORDEF_( Invalid_Argument,                            0x06, \
                "invalid argument" )
  FT_ERRORDEF_( Unimplemented_Feature,                       0x07, \
                "unimplemented feature" )
  FT_ERRORDEF_( Invalid_Table,                               0x08, \
                "broken table" )
  FT_ERRORDEF_( Invalid_Offset,                              0x09, \
                "broken offset within table" )
  FT_ERRORDEF_( Array_Too_Large,                             0x0A, \
                "array allocation size too large" )

  /* glyph/character errors */

  FT_ERRORDEF_( Invalid_Glyph_Index,                         0x10, \
                "invalid glyph index" )
  FT_ERRORDEF_( Invalid_Character_Code,                      0x11, \
                "invalid character code" )
  FT_ERRORDEF_( Invalid_Glyph_Format,                        0x12, \
                "unsupported glyph image format" )
  FT_ERRORDEF_( Cannot_Render_Glyph,                         0x13, \
                "cannot render this glyph format" )
  FT_ERRORDEF_( Invalid_Outline,                             0x14, \
                "invalid outline" )
  FT_ERRORDEF_( Invalid_Composite,                           0x15, \
                "invalid composite glyph" )
  FT_ERRORDEF_( Too_Many_Hints,                              0x16, \
                "too many hints" )
  FT_ERRORDEF_( Invalid_Pixel_Size,                          0x17, \
                "invalid pixel size" )

  /* handle errors */

  FT_ERRORDEF_( I