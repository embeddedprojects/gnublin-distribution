#ifndef PCA9555_H_
#define PCA9555_H_

   /* Includes: */
  // #include <stdio.h>
   //#include <linux/i2c-dev.h> //prüfen
   //#include <sys/ioctl.h>     //prüfen
   //#include <sys/types.h>     //prüfen
   //#include <sys/stat.h>      //prüfen
   //#include <fcntl.h>         //prüfen
   
   #include "i2c_dev.h"
    
   
   /* Typdefs: */
   
   typedef struct {
      i2c_dev *i2c_dev_tmp;
      char *module;
      int debug;
   } pca9555;
  
   
   /* Functions: */
   int pca9555_init(pca9555 *pca9555_tmp, char *filename_tmp, int addr_tmp, 
                    int debug_tmp );
   int pca9555_show_addr(pca9555 *pca9555_tmp, char *filename_tmp, int print);
   int pca9555_get_port_dir(pca9555 *pca9555_tmp, unsigned char *port_tmp);
   int pca9555_set_port_dir(pca9555 *pca9555_tmp, unsigned char bit_tmp,unsigned char port_tmp, unsigned char value_tmp);      
   int pca9555_get_port_val(pca9555 *pca9555_tmp,unsigned char *port_tmp);
   int pca9555_get_pin_val(pca9555 *pca9555_tmp, unsigned char bit_tmp, unsigned char port_tmp);
   int pca9555_set_port_val(pca9555 *pca9555_tmp, unsigned char bit_tmp,unsigned char port_tmp, unsigned char value_tmp);
   int pca9555_change_addr(pca9555 *pca9555_tmp, int addr_tmp);

   /* Makros: */
   
   #define PORT0_DIRECTION 0x06
   #define PORT1_DIRECTION 0x07
   
   #define PORT0_VALUE     0x02
   #define PORT1_VALUE     0x03
   


#endif
