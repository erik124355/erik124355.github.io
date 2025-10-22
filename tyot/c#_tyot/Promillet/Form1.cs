namespace Promillet
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }
        class Laskut
        {
            private double paino;
            private double juotu;
            private double aika;

            public Laskut(double pa, double ju, double ai)
            {
                if (ai < 0 || pa < 0 || ju < 0) { MessageBox.Show("Virhe! anna Kelvolliset arvot!"); return; }

                paino = pa;
                juotu = ju;
                aika = ai;
            }
            public double laskeLitrat()
            {
                return juotu * 0.3;
            }
            public double laskeGrammat()
            {
                return (laskeLitrat() * 8) * 4.5;
            }
            public double laskePalamisvauhti()
            {
                return paino / 10;
            }
            public double laskePalaminenYhteensä()
            {
                return laskePalamisvauhti() * aika;
            }
            public double laskeJaljellaOlevaAlkoholi()
            {
                double jaljella = laskeGrammat() - laskePalaminenYhteensä();
                return (jaljella > 0)? jaljella : 0;
            }
            public double lasMiehet()
            {
                return laskeJaljellaOlevaAlkoholi() / (paino * 0.7);
            }
            public double lasNaiset()
            {
                return laskeJaljellaOlevaAlkoholi() / (paino * 0.6);
            }
        }


        private void button1_Click(object sender, EventArgs e)
        {
            try
            {
                double pa = double.Parse(textBox1.Text);
                double ju = double.Parse(textBox2.Text);
                double ai = double.Parse(textBox3.Text);

                Laskut lasku = new Laskut(pa, ju, ai);

                if (radioButton1.Checked) { double naiset = lasku.lasNaiset(); textBox4.Text = naiset.ToString("F2"); }
                if (radioButton2.Checked) { double miehet = lasku.lasMiehet(); textBox4.Text = miehet.ToString("F2"); }
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }

        private void Form1_Load(object sender, EventArgs e)
        {

        }
    }
}
