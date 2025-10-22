namespace pinta_alat_olio
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        public void cbValinta()
        {
            if (checkBox1.Checked)
            {
                txtSivu1.Visible = true;
                txtSivu1.Text = "";
                txtKorkeus1.Visible = false;
                txtKorkeus1.Text = 0.ToString();
                txtSade1.Visible = false;
                txtSade1.Text = 0.ToString();
                label2.Visible = false;
                label3.Visible = false;
                label4.Visible = true;
            }
            if (checkBox2.Checked)
            {
                txtSivu1.Visible = false;
                txtSivu1.Text = 0.ToString();
                txtKorkeus1.Visible = false;
                txtKorkeus1.Text = 0.ToString();
                txtSade1.Visible = true;
                txtSade1.Text = "";
                label2.Visible = true;
                label3.Visible = false;
                label4.Visible = false;
            }
            if (checkBox3.Checked)
            {
                txtSivu1.Visible = false;
                txtSivu1.Text = 0.ToString ();
                txtKorkeus1.Visible = true;
                txtKorkeus1.Text = "";
                txtSade1.Visible = true;
                txtSade1.Text = "";
                label2.Visible = true;
                label3.Visible = true;
                label4.Visible = false;
            }
            if (checkBox4.Checked)
            {
                txtSivu1.Visible = false;
                txtSivu1.Text = 0.ToString ();
                txtKorkeus1.Visible = false;
                txtKorkeus1.Text = 0.ToString();
                txtSade1.Visible = true;
                txtSade1.Text = "";
                label2.Visible = true;
                label3.Visible = false;
                label4.Visible = false;
            }
        }
        public void cbValinta1()
        {
            if (checkBox5.Checked)
            {
                txtSade2.Visible = true;
                txtSade2.Text = "";
                txtKorkeus2.Visible = false;
                txtKorkeus2.Text = 0.ToString ();
                txtSivu2.Visible = false;
                txtSivu2.Text = 0.ToString ();
                label7.Visible = true;
                label6.Visible = false;
                label5.Visible = false;
            }
            if (checkBox6.Checked)
            {
                txtSade2.Visible = false;
                txtSade2.Text = 0.ToString ();
                txtKorkeus2.Visible = false;
                txtKorkeus2.Text = 0.ToString ();
                txtSivu2.Visible = true;
                txtSivu2.Text = "";
                label7.Visible = false;
                label6.Visible = false;
                label5.Visible = true;
            }
            if (checkBox7.Checked)
            {
                txtSade2.Visible = true;
                txtSade2.Text = "";
                txtKorkeus2.Visible = true;
                txtKorkeus2.Text = "";
                txtSivu2.Visible = false;
                txtSivu2.Text = 0.ToString();
                label7.Visible = true;
                label6.Visible = true;
                label5.Visible = false;
            }
            if (checkBox8.Checked)
            {
                txtSade2.Visible = false;
                txtSade2.Text = 0.ToString ();
                txtKorkeus2.Visible = true;
                txtKorkeus2.Text = "";
                txtSivu2.Visible = true;
                txtSivu2.Text = "";
                label7.Visible = false;
                label6.Visible = true;
                label5.Visible = true;
            }
        }

        class cLaskut
        {
            private double p_sade;
            private double p_korkeus;
            private double p_sivu;

            public cLaskut(double sa, double ko, double si)
            {
                if (sa < 0 || ko < 0 || si < 0) { MessageBox.Show("Virhe! anna positiiviset arvot!"); return; }

                p_sade = sa;
                p_korkeus = ko;
                p_sivu = si;
            }

            public double laskeNelio()
            {
                return Math.Pow(p_sivu, 2);
            }
            public double laskeYmpyra()
            {
                return Math.PI * Math.Pow(p_sade, 2);
            }
            public double laskeYmpyralierio()
            {
                return 2 * Math.PI * p_sade * p_korkeus + 2 * Math.PI * Math.Pow(p_sade, 2);
            }
            public double laskePallo()
            {
                return 4 * Math.PI * Math.Pow(p_sade, 2);
            }

            public double laskePallo1()
            {
                return (4.0/3.0) * Math.PI * Math.Pow(p_sade, 3);
            }
            public double laskeKuutio()
            {
                return Math.Pow(p_sivu, 3);
            }
            public double laskeYmpyraKartio()
            {
                return (1.0 / 3.0) * Math.PI * Math.Pow(p_sade, 2) * p_korkeus;
            }
            public double laskePyramidi()
            {
                return (1.0/3.0) * Math.Pow(p_sivu, 2) * p_korkeus;
            }

        }

        private void Form1_Load(object sender, EventArgs e)
        {

        }

        private void button1_Click(object sender, EventArgs e)
        {
            try
            {
                double sa = double.Parse(txtSade1.Text);
                double ko = double.Parse(txtKorkeus1.Text);
                double si = double.Parse(txtSivu1.Text);

                cLaskut Laskut = new cLaskut(sa, ko, si);

               
                if (checkBox1.Checked) { double Nelio = Laskut.laskeNelio(); txtVastaus1.Text = Nelio.ToString("F2"); }
                if (checkBox2.Checked) { double Ympyra = Laskut.laskeYmpyra(); txtVastaus1.Text = Ympyra.ToString("F2"); }
                if (checkBox3.Checked) { double Ympyralierio = Laskut.laskeYmpyralierio(); txtVastaus1.Text = Ympyralierio.ToString("F2"); }
                if (checkBox4.Checked) { double Pallo = Laskut.laskePallo(); txtVastaus1.Text = Pallo.ToString("F2"); }
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }


        private void button2_Click(object sender, EventArgs e)
        {
            try
            {
                double sa = double.Parse(txtSade2.Text);
                double ko = double.Parse(txtKorkeus2.Text);
                double si = double.Parse(txtSivu2.Text);

                cLaskut Laskut = new cLaskut(sa, ko, si);

                if (checkBox5.Checked) { double Pallo1 = Laskut.laskePallo1(); txtVastaus2.Text = Pallo1.ToString("F2"); }
                if (checkBox6.Checked) { double Kuutio = Laskut.laskeKuutio(); txtVastaus2.Text = Kuutio.ToString("F2"); }
                if (checkBox7.Checked) { double YmpyraKartio = Laskut.laskeYmpyraKartio(); txtVastaus2.Text = YmpyraKartio.ToString("F2"); }
                if (checkBox8.Checked) { double Pyramidi = Laskut.laskePyramidi(); txtVastaus2.Text = Pyramidi.ToString("F2"); }
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }

        private void button3_Click(object sender, EventArgs e)
        {
            int cblist = 0;

           
            if (checkBox1.Checked) { cblist++; }
            if (checkBox2.Checked) { cblist++; }
            if (checkBox3.Checked) { cblist++; }
            if (checkBox4.Checked) { cblist++; }

            if (cblist > 1) { MessageBox.Show("Virhe! Valiste vain 1 laatikko!"); return; }

            cbValinta();       
        }

        private void button4_Click(object sender, EventArgs e)
        {
            int cblist = 0;


            if (checkBox5.Checked) { cblist++; }
            if (checkBox6.Checked) { cblist++; }
            if (checkBox7.Checked) { cblist++; }
            if (checkBox8.Checked) { cblist++; }

            if (cblist > 1) { MessageBox.Show("Virhe! Valiste vain 1 laatikko!"); return; }

            cbValinta1();
        }
    }
}
