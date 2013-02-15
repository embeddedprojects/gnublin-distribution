#include "pca9555.h"

/*
int main(void)
{
   int file,i;
   char filename[40]; 
   char buf[2];
   char test;
   
   int addr = 0b00100000;

   pca9555 pca9555_tmp, *ppca9555_tmp;
   ppca9555_tmp = &pca9555_tmp;  


   pca9555_init(ppca9555_tmp, "/dev/i2c-1", 0x20);
   
   for(i = 0; i < 8; i++){
      printf("%d",i);
      pca9555_set_port_dir(ppca9555_tmp, i, 0x06, 0);
   }
   
   pca9555_set_port_val(ppca9555_tmp, 0x02, 0x02, 0);
   
  // (pca9555 *pca9555_tmp, unsigned char bit_tmp, 
  //                       unsigned char port_tmp, unsigned char value_tmp);
     

   return 0;       

}
*/

int pca9555_show_addr(pca9555 *pca9555_tmp, char *filename_tmp, int print)
{  
   //ToDo
   // i2c_get_add arbeitet nicht so wie gedacht !
   
   int buf_tmp[8],i , j;
   
   i2c_get_addr(filename_tmp, 1, 1, buf_tmp,pca9555_tmp->i2c_dev_tmp);
   if(print)
   {
      printf("\n");
      printf("MOD:ADDR\n");
      printf("\n");
   }
   for(i=0; i < 8; i++)
   {
      if(buf_tmp[i] == 1)
      {  
         if(print)
            printf("%d:0x%02x \n",j, i+32 , buf_tmp[i]);
         pca9555_tmp->module[j] = i+32;
         j++;
      }
   }
   if(print)
      printf("\n");
 
   return 0;
}

int pca9555_init(pca9555 *pca9555_tmp, char *filename_tmp, int addr_tmp, 
                 int debug_tmp )
{

  /*
   * locals
   */
   static i2c_dev i2c_dev_tmp;
   static char module_buf[8];
   i2c_dev *p_i2c_dev_tmp;
   pca9555_tmp->debug = debug_tmp;


   p_i2c_dev_tmp = &i2c_dev_tmp;
   pca9555_tmp->i2c_dev_tmp = p_i2c_dev_tmp;
   pca9555_tmp->module = module_buf;
   
   if(i2c_init(pca9555_tmp->i2c_dev_tmp, filename_tmp, addr_tmp) < 0)
   {
     if(pca9555_tmp->debug)
        printf("I2C error\n");
     return -1;
   }
   
   return 0;
    

}

int pca9555_change_addr(pca9555 *pca9555_tmp, int addr_tmp)
{
   if(i2c_change_addr(pca9555_tmp->i2c_dev_tmp, addr_tmp ) < 0)
   {
     if(pca9555_tmp->debug)
        printf("I2C error\n");
     return -1;   
   }
   
   return 0;

}

int pca9555_set_port_dir(pca9555 *pca9555_tmp, unsigned char bit_tmp, 
                         unsigned char port_tmp, unsigned char value_tmp)
{
   unsigned char buf_tmp[2];
   unsigned char rec_tmp;
   
   if( (PORT0_DIRECTION == port_tmp) || (PORT1_DIRECTION == port_tmp) )
   {  
      rec_tmp = port_tmp;
      printf("", rec_tmp);
      if(pca9555_get_port_dir(pca9555_tmp, &rec_tmp) < 0)
      {
      if(pca9555_tmp->debug)
          printf("I2C error\n");
      return -1;         
      }
      if(value_tmp == 0)
      {
         rec_tmp  &= ~(1 << bit_tmp); //clear bit
      }
      else
      { 
         rec_tmp |=  (1 << bit_tmp); // set bit

      }
      buf_tmp[0] = port_tmp;
      buf_tmp[1] = rec_tmp;
      if(i2c_write(pca9555_tmp->i2c_dev_tmp, buf_tmp, 
                ( sizeof(buf_tmp)/sizeof(unsigned char)) ) < 0 )
      {
      if(pca9555_tmp->debug)
          printf("I2C error\n");
      return -1;         
                
      }
      
 
   }
      return 0;
}

int pca9555_get_port_dir(pca9555 *pca9555_tmp, unsigned char *port_tmp)
{
   
   unsigned char buf_tmp[1];
   
   if( (PORT0_DIRECTION == *port_tmp) || (PORT1_DIRECTION == *port_tmp) )
   {
      if(i2c_write(pca9555_tmp->i2c_dev_tmp,buf_tmp, 
                (sizeof(buf_tmp) /sizeof(unsigned char))) < 0)
      {
         if(pca9555_tmp->debug)
             printf("I2C error\n");
          return -1;         
                
      }
      if(i2c_read(pca9555_tmp->i2c_dev_tmp, buf_tmp, 
                (sizeof(buf_tmp) /sizeof(unsigned char)))< 0)
      {
         if(pca9555_tmp->debug)
             printf("I2C error\n");
         return -1;         
      }
      *port_tmp = buf_tmp[0];
      return 0;
   }
   else
      return -1;

}

int pca9555_get_port_val(pca9555 *pca9555_tmp, unsigned char *port_tmp)
{
   
   unsigned char buf_tmp[1];
   
   if( (PORT0_VALUE == *port_tmp) || (PORT1_VALUE == *port_tmp) )
   {
      if(i2c_write(pca9555_tmp->i2c_dev_tmp,buf_tmp, 
                (sizeof(buf_tmp) /sizeof(unsigned char))) < 0 )
      {
         if(pca9555_tmp->debug)
             printf("I2C error\n");
         return -1;         
      }          
                
                
      i2c_read(pca9555_tmp->i2c_dev_tmp, buf_tmp, 
               (sizeof(buf_tmp) /sizeof(unsigned char)));
               
               
               
      *port_tmp = buf_tmp[0];
      return 0;
   }
   else
      return -1;

}

int pca9555_set_port_val(pca9555 *pca9555_tmp, unsigned char bit_tmp, 
                         unsigned char port_tmp, unsigned char value_tmp)
{
   unsigned char buf_tmp[2];
   unsigned char rec_tmp;
   
   if( (PORT0_VALUE == port_tmp) || (PORT1_VALUE == port_tmp) )
   {  
      rec_tmp = port_tmp;
      printf("", rec_tmp);
      pca9555_get_port_val(pca9555_tmp, &rec_tmp);
      //printf("rec port 0x%02x\n", rec_tmp);
      if(value_tmp == 0)
      {
         rec_tmp  &= ~(1 << bit_tmp); //clear bit
      }
      else
      {
         rec_tmp |=  (1 << bit_tmp); // set bit
      
      }
      //printf("set port 0x%02x\n", rec_tmp);
      buf_tmp[0] = port_tmp;
      buf_tmp[1] = rec_tmp;
      i2c_write(pca9555_tmp->i2c_dev_tmp, buf_tmp, 
                ( sizeof(buf_tmp)/sizeof(unsigned char)) );
      
 
   }
      return 0;
}

int pca9555_get_pin_val(pca9555 *pca9555_tmp, unsigned char bit_tmp, unsigned char port_tmp)
{
   unsigned char buf_tmp[2];
   unsigned char rec_tmp;
   
   if( (PORT0_VALUE == port_tmp) || (PORT1_VALUE == port_tmp) )
   {  

     // printf("rec port 0x%02x\n", rec_tmp);
     // printf("bit_tmp port 0x%02x\n", bit_tmp);
      rec_tmp = port_tmp;
      printf("", rec_tmp);
      pca9555_get_port_val(pca9555_tmp, &rec_tmp);

      if (rec_tmp & (1 << bit_tmp))
	return 1;
      else
	return 0;   
   }
      return 0;
}