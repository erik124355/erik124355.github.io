namespace Korttipakka
{
    public partial class Form1 : Form
    {
        int[] kortit = new int[10];
        public Form1()
        {
            InitializeComponent();
        }
        private void kortitJärjestys()
        {
            kortit[0] = 1;
            kortit[1] = 2;
            kortit[2] = 3;
            kortit[3] = 4;
            kortit[4] = 5;
            kortit[5] = 6;
            kortit[6] = 7;
            kortit[7] = 8;
            kortit[8] = 9;
            kortit[9] = 10;

            textBox1.Text = "";
            textBox1.Text += "Korttien järjestys on: ";

            for (int i = 0; i < kortit.Length; i++) { textBox1.Text += kortit[i] + " "; }
        }
        private void kortitRandom()
        {
            Random random = new Random();

            kortit[0] = 1;
            kortit[1] = 2;
            kortit[2] = 3;
            kortit[3] = 4;
            kortit[4] = 5;
            kortit[5] = 6;
            kortit[6] = 7;
            kortit[7] = 8;
            kortit[8] = 9;
            kortit[9] = 10;

            textBox1.Text = "";
            textBox1.Text += "Korttien järjestys on: ";

            for (int i = 0; i < kortit.Length; i++) 
            {
                int r = random.Next(i, kortit.Length);
                int vastaus = kortit[i];
                kortit[i] = kortit[r];
                kortit[r] = vastaus;
            }

            for (int i = 0;i < kortit.Length;i++)
            {
                textBox1.Text += kortit[i] + " ";
            }
        }

        private void button1_Click(object sender, EventArgs e)
        {
            kortitJärjestys();
        }

        private void button2_Click(object sender, EventArgs e)
        {
            kortitRandom();
        }
    }
}
