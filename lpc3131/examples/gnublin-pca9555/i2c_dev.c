#include "i2c_dev.h"



/*
int main()
{

   int i;
   int buf[40];
   i2c_get_addr("/dev/i2c-1",1,1,buf);
   for(i=0; i < 8; i++)
   {
      printf("%d ",buf[i]);
   }
   return 0;

}
*/




int i2c_init(i2c_dev *i2c_dev_tmp, char *filename_tmp, int addr_tmp)
{

  /*
   *locals
   */
   i2c_dev_tmp->filename  = filename_tmp;
   i2c_dev_tmp->addr      = addr_tmp;
  // i2c_dev_tmp->debug     = debug_tmp;
   

   if((i2c_dev_tmp->file = open(i2c_dev_tmp->filename, O_RDWR)) < 0 )
   {
      if(i2c_dev_tmp->debug)
         printf("Can not open file\n");
      return -1;
   } 
   
   if(ioctl(i2c_dev_tmp->file, I2C_SLAVE, i2c_dev_tmp->addr) < 0)
   {
      if(i2c_dev_tmp->debug)
         printf("Can not set adress\n");
      return -1;
   }
   
   return 0;
  
}


int i2c_get_addr( char *filename_tmp, int addr_beg, int addr_end, int *buf_tmp, i2c_dev *i2c_dev_tmp)
{
   // Kann momentan nur mit pca9555 betrieben werden
   // ToDO -> für alle I2c - Slaves
   // ToDO begin / end anpassen
   // ToDO debug
   // ausgabe englisch
   int i,j,file, res;
   char buf_reg[] = {0x06};
   
   //i2c_smbus_access(file, I2C_SMBUS_WRITE, 0, I2C_SMBUS_QUICK, NULL);
   
   
   if((file = open(filename_tmp, O_RDWR)) < 0 )
   {
      printf("Fehler beim Oefnen\n");
      return 0;
   } 
   
   for(i = 32; i < 40; i++)
   {
      if(ioctl(file, I2C_SLAVE, i) < 0)
      {
         if(errno == EBUSY)
         {
	    if(i2c_dev_tmp->debug)
               printf("Fehler bei %02x\n",i);
            continue;
         }
         else
         {  
	    if(i2c_dev_tmp->debug)
               printf("Fehler beim Setzen der addr\n");
	    return -1;
         }
         
      }
      
      
      if(res = write(file,buf_reg , 1)  < 0 )
      {
         *(buf_tmp+i-32) = 0;
         continue;
      }
      else
      {
         *(buf_tmp+i-32) = 1;
      }   
   }
   
   return 0;
}

int i2c_change_addr(i2c_dev *i2c_dev_tmp, int addr_tmp )
{  
  /*
   *locals
   */
   i2c_dev_tmp->addr = addr_tmp;
   
   if(ioctl(i2c_dev_tmp->file, I2C_SLAVE, i2c_dev_tmp->addr) < 0)
   {
      if(i2c_dev_tmp->debug)
         printf("Can not set adress\n");
      return -1;
   }
   
   return 0;
}


   

int i2c_write(i2c_dev *i2c_dev_tmp, char *buf_tmp, int length_buf)
{

   if(write(i2c_dev_tmp->file, buf_tmp, length_buf) != length_buf )
   {
      if(i2c_dev_tmp->debug)
         printf("Failed to writing on IC2 bus\n");
      return -1;
   }
   
   return 0;
   
}

int i2c_read(i2c_dev *i2c_dev_tmp, char *buf_tmp, int length_buf)
{

   if(read(i2c_dev_tmp->file, buf_tmp , length_buf) != length_buf)
   {
      if(i2c_dev_tmp->debug)
         printf("Failed to reading on IC2 bus\n");
      return -1;
   }

   return 0;
}

