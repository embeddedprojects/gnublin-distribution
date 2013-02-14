#ifndef I2C_DEV_H_
#define I2C_DEV_H_

   /* Includes: */
   
   #include <stdio.h>
   #include <errno.h>
   #include <linux/i2c-dev.h> //prüfen
   #include <sys/ioctl.h>     //prüfen
   #include <sys/types.h>     //prüfen
   #include <sys/stat.h>      //prüfen
   #include <fcntl.h>         //prüfen
   #include <string.h>
   #include <stdlib.h>
   #include <unistd.h>

   
   /* Typdefs: */
   
   typedef struct {
      int file;
      char *filename;  
      int addr;
      int debug;
   } i2c_dev;
   
   
   /* Functions: */
   
   int i2c_init(i2c_dev *i2c_dev_tmp, char *filename_tmp, int addr_tmp);

   int i2c_write(i2c_dev *i2c_dev_tmp, char *buf,          int length_buf);
   int i2c_read(i2c_dev *i2c_dev_tmp,  char *buf_tmp,      int length_buf);
   int i2c_get_addr(char *filename_tmp, int addr_beg, int addr_end, 
                    int *buf_tmp,i2c_dev *i2c_dev_tmp);
   int i2c_change_addr(i2c_dev *i2c_dev_tmp, int addr_tmp );
   
   /* Makros: */


   
   


#endif
