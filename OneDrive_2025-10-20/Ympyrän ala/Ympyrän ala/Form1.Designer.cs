namespace Ympyrän_ala
{
    partial class Form1
    {
        /// <summary>
        ///  Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        ///  Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        ///  Required method for Designer support - do not modify
        ///  the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            pictureBox1 = new PictureBox();
            txtSade = new TextBox();
            label1 = new Label();
            button1 = new Button();
            txtVastaus = new TextBox();
            ((System.ComponentModel.ISupportInitialize)pictureBox1).BeginInit();
            SuspendLayout();
            // 
            // pictureBox1
            // 
            pictureBox1.Image = Properties.Resources.circle;
            pictureBox1.Location = new Point(198, 44);
            pictureBox1.Margin = new Padding(2, 2, 2, 2);
            pictureBox1.Name = "pictureBox1";
            pictureBox1.Size = new Size(596, 518);
            pictureBox1.TabIndex = 0;
            pictureBox1.TabStop = false;
            // 
            // txtSade
            // 
            txtSade.Location = new Point(674, 361);
            txtSade.Margin = new Padding(2, 2, 2, 2);
            txtSade.Name = "txtSade";
            txtSade.Size = new Size(121, 27);
            txtSade.TabIndex = 1;
            txtSade.Text = "3";
            txtSade.TextChanged += txtSade_TextChanged;
            // 
            // label1
            // 
            label1.AutoSize = true;
            label1.Font = new Font("Segoe UI", 14F);
            label1.Location = new Point(347, 11);
            label1.Margin = new Padding(2, 0, 2, 0);
            label1.Name = "label1";
            label1.Size = new Size(206, 32);
            label1.TabIndex = 2;
            label1.Text = "Ympyrän Pinta-ala";
            // 
            // button1
            // 
            button1.Location = new Point(662, 518);
            button1.Margin = new Padding(2, 2, 2, 2);
            button1.Name = "button1";
            button1.Size = new Size(90, 27);
            button1.TabIndex = 3;
            button1.Text = "Laske";
            button1.UseVisualStyleBackColor = true;
            button1.Click += button1_Click;
            // 
            // txtVastaus
            // 
            txtVastaus.Location = new Point(649, 566);
            txtVastaus.Margin = new Padding(2, 2, 2, 2);
            txtVastaus.Name = "txtVastaus";
            txtVastaus.Size = new Size(121, 27);
            txtVastaus.TabIndex = 4;
            // 
            // Form1
            // 
            AutoScaleDimensions = new SizeF(8F, 20F);
            AutoScaleMode = AutoScaleMode.Font;
            ClientSize = new Size(910, 679);
            Controls.Add(txtVastaus);
            Controls.Add(button1);
            Controls.Add(label1);
            Controls.Add(txtSade);
            Controls.Add(pictureBox1);
            Margin = new Padding(2, 2, 2, 2);
            Name = "Form1";
            Text = "Form1";
            ((System.ComponentModel.ISupportInitialize)pictureBox1).EndInit();
            ResumeLayout(false);
            PerformLayout();
        }

        #endregion

        private PictureBox pictureBox1;
        private TextBox txtSade;
        private Label label1;
        private Button button1;
        private TextBox txtVastaus;
    }
}
