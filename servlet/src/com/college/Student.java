package com.college;
public class Student {
    private int id;
    private String name, rollNumber, email, course;
    public Student(int id, String name, String rollNumber, String email, String course) {
        this.id=id; this.name=name; this.rollNumber=rollNumber; this.email=email; this.course=course;
    }
    public int getId(){return id;} public String getName(){return name;}
    public String getRollNumber(){return rollNumber;} public String getEmail(){return email;}
    public String getCourse(){return course;}
}
